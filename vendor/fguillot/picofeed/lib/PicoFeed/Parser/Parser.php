<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;
use PicoFeed\Client\Url;
use PicoFeed\Encoding\Encoding;
use PicoFeed\Filter\Filter;
use PicoFeed\Logging\Logger;
use PicoFeed\Scraper\Scraper;

/**
 * Base parser class.
 *
 * @author  Frederic Guillot
 */
abstract class Parser
{
    /**
     * Config object.
     *
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * DateParser object.
     *
     * @var \PicoFeed\Parser\DateParser
     */
    protected $date;

    /**
     * Hash algorithm used to generate item id, any value supported by PHP, see hash_algos().
     *
     * @var string
     */
    private $hash_algo = 'sha256';

    /**
     * Feed content (XML data).
     *
     * @var string
     */
    protected $content = '';

    /**
     * Fallback url.
     *
     * @var string
     */
    protected $fallback_url = '';

    /**
     * XML namespaces supported by parser.
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * XML namespaces used in document.
     *
     * @var array
     */
    protected $used_namespaces = array();

    /**
     * Enable the content filtering.
     *
     * @var bool
     */
    private $enable_filter = true;

    /**
     * Enable the content grabber.
     *
     * @var bool
     */
    private $enable_grabber = false;

    /**
     * Enable the content grabber on all pages.
     *
     * @var bool
     */
    private $grabber_needs_rule_file = false;

    /**
     * Ignore those urls for the content scraper.
     *
     * @var array
     */
    private $grabber_ignore_urls = array();

    /**
     * Constructor.
     *
     * @param string $content       Feed content
     * @param string $http_encoding HTTP encoding (headers)
     * @param string $fallback_url  Fallback url when the feed provide relative or broken url
     */
    public function __construct($content, $http_encoding = '', $fallback_url = '')
    {
        $this->date = new DateParser();
        $this->fallback_url = $fallback_url;
        $xml_encoding = XmlParser::getEncodingFromXmlTag($content);

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($content);

        // Encode everything in UTF-8
        Logger::setMessage(get_called_class().': HTTP Encoding "'.$http_encoding.'" ; XML Encoding "'.$xml_encoding.'"');
        $this->content = Encoding::convert($this->content, $xml_encoding ?: $http_encoding);
    }

    /**
     * Parse the document.
     *
     * @return \PicoFeed\Parser\Feed
     */
    public function execute()
    {
        Logger::setMessage(get_called_class().': begin parsing');

        $xml = XmlParser::getSimpleXml($this->content);

        if ($xml === false) {
            Logger::setMessage(get_called_class().': Applying XML workarounds');
            $this->content = Filter::normalizeData($this->content);
            $xml = XmlParser::getSimpleXml($this->content);

            if ($xml === false) {
                Logger::setMessage(get_called_class().': XML parsing error');
                Logger::setMessage(XmlParser::getErrors());
                throw new MalformedXmlException('XML parsing error');
            }
        }

        $this->used_namespaces = $xml->getNamespaces(true);
        $xml = $this->registerSupportedNamespaces($xml);

        $feed = new Feed();

        $this->findFeedUrl($xml, $feed);
        $this->checkFeedUrl($feed);

        $this->findSiteUrl($xml, $feed);
        $this->checkSiteUrl($feed);

        $this->findFeedTitle($xml, $feed);
        $this->findFeedDescription($xml, $feed);
        $this->findFeedLanguage($xml, $feed);
        $this->findFeedId($xml, $feed);
        $this->findFeedDate($xml, $feed);
        $this->findFeedLogo($xml, $feed);
        $this->findFeedIcon($xml, $feed);

        foreach ($this->getItemsTree($xml) as $entry) {
            $entry = $this->registerSupportedNamespaces($entry);

            $item = new Item();
            $item->xml = $entry;
            $item->namespaces = $this->used_namespaces;

            $this->findItemAuthor($xml, $entry, $item);

            $this->findItemUrl($entry, $item);
            $this->checkItemUrl($feed, $item);

            $this->findItemTitle($entry, $item);
            $this->findItemContent($entry, $item);

            // Id generation can use the item url/title/content (order is important)
            $this->findItemId($entry, $item, $feed);

            $this->findItemDate($entry, $item, $feed);
            $this->findItemEnclosure($entry, $item, $feed);
            $this->findItemLanguage($entry, $item, $feed);

            // Order is important (avoid double filtering)
            $this->filterItemContent($feed, $item);
            $this->scrapWebsite($item);

            $feed->items[] = $item;
        }

        Logger::setMessage(get_called_class().PHP_EOL.$feed);

        return $feed;
    }

    /**
     * Check if the feed url is correct.
     *
     * @param Feed $feed Feed object
     */
    public function checkFeedUrl(Feed $feed)
    {
        if ($feed->getFeedUrl() === '') {
            $feed->feed_url = $this->fallback_url;
        } else {
            $feed->feed_url = Url::resolve($feed->getFeedUrl(), $this->fallback_url);
        }
    }

    /**
     * Check if the site url is correct.
     *
     * @param Feed $feed Feed object
     */
    public function checkSiteUrl(Feed $feed)
    {
        if ($feed->getSiteUrl() === '') {
            $feed->site_url = Url::base($feed->getFeedUrl());
        } else {
            $feed->site_url = Url::resolve($feed->getSiteUrl(), $this->fallback_url);
        }
    }

    /**
     * Check if the item url is correct.
     *
     * @param Feed $feed Feed object
     * @param Item $item Item object
     */
    public function checkItemUrl(Feed $feed, Item $item)
    {
        $item->url = Url::resolve($item->getUrl(), $feed->getSiteUrl());
    }

    /**
     * Fetch item content with the content grabber.
     *
     * @param Item $item Item object
     */
    public function scrapWebsite(Item $item)
    {
        if ($this->enable_grabber && !in_array($item->getUrl(), $this->grabber_ignore_urls)) {
            $grabber = new Scraper($this->config);
            $grabber->setUrl($item->getUrl());

            if ($this->grabber_needs_rule_file) {
                $grabber->disableCandidateParser();
            }

            $grabber->execute();

            if ($grabber->hasRelevantContent()) {
                $item->content = $grabber->getFilteredContent();
            }
        }
    }

    /**
     * Filter HTML for entry content.
     *
     * @param Feed $feed Feed object
     * @param Item $item Item object
     */
    public function filterItemContent(Feed $feed, Item $item)
    {
        if ($this->isFilteringEnabled()) {
            $filter = Filter::html($item->getContent(), $feed->getSiteUrl());
            $filter->setConfig($this->config);
            $item->content = $filter->execute();
        } else {
            Logger::setMessage(get_called_class().': Content filtering disabled');
        }
    }

    /**
     * Generate a unique id for an entry (hash all arguments).
     *
     * @return string
     */
    public function generateId()
    {
        return hash($this->hash_algo, implode(func_get_args()));
    }

    /**
     * Return true if the given language is "Right to Left".
     *
     * @static
     *
     * @param string $language Language: fr-FR, en-US
     *
     * @return bool
     */
    public static function isLanguageRTL($language)
    {
        $language = strtolower($language);

        $rtl_languages = array(
            'ar', // Arabic (ar-**)
            'fa', // Farsi (fa-**)
            'ur', // Urdu (ur-**)
            'ps', // Pashtu (ps-**)
            'syr', // Syriac (syr-**)
            'dv', // Divehi (dv-**)
            'he', // Hebrew (he-**)
            'yi', // Yiddish (yi-**)
        );

        foreach ($rtl_languages as $prefix) {
            if (strpos($language, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set Hash algorithm used for id generation.
     *
     * @param string $algo Algorithm name
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function setHashAlgo($algo)
    {
        $this->hash_algo = $algo ?: $this->hash_algo;

        return $this;
    }

    /**
     * Set a different timezone.
     *
     * @see    http://php.net/manual/en/timezones.php
     *
     * @param string $timezone Timezone
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function setTimezone($timezone)
    {
        if ($timezone) {
            $this->date->timezone = $timezone;
        }

        return $this;
    }

    /**
     * Set config object.
     *
     * @param \PicoFeed\Config\Config $config Config instance
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Enable the content grabber.
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function disableContentFiltering()
    {
        $this->enable_filter = false;
    }

    /**
     * Return true if the content filtering is enabled.
     *
     * @return bool
     */
    public function isFilteringEnabled()
    {
        if ($this->config === null) {
            return $this->enable_filter;
        }

        return $this->config->getContentFiltering($this->enable_filter);
    }

    /**
     * Enable the content grabber.
     *
     * @param bool $needs_rule_file true if only pages with rule files should be
     *                              scraped
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function enableContentGrabber($needs_rule_file = false)
    {
        $this->enable_grabber = true;
        $this->grabber_needs_rule_file = $needs_rule_file;
    }

    /**
     * Set ignored URLs for the content grabber.
     *
     * @param array $urls URLs
     *
     * @return \PicoFeed\Parser\Parser
     */
    public function setGrabberIgnoreUrls(array $urls)
    {
        $this->grabber_ignore_urls = $urls;
    }

    /**
     * Register all supported namespaces to be used within an xpath query.
     *
     * @param SimpleXMLElement $xml Feed xml
     *
     * @return SimpleXMLElement
     */
    public function registerSupportedNamespaces(SimpleXMLElement $xml)
    {
        foreach ($this->namespaces as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix, $ns);
        }

        return $xml;
    }

    /**
     * Find the feed url.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedUrl(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the site url.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findSiteUrl(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed title.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedTitle(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed description.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedDescription(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed language.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedLanguage(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed id.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedId(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed date.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedDate(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed logo url.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedLogo(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed icon.
     *
     * @param SimpleXMLElement      $xml  Feed xml
     * @param \PicoFeed\Parser\Feed $feed Feed object
     */
    abstract public function findFeedIcon(SimpleXMLElement $xml, Feed $feed);

    /**
     * Get the path to the items XML tree.
     *
     * @param SimpleXMLElement $xml Feed xml
     *
     * @return SimpleXMLElement
     */
    abstract public function getItemsTree(SimpleXMLElement $xml);

    /**
     * Find the item author.
     *
     * @param SimpleXMLElement      $xml   Feed
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     */
    abstract public function findItemAuthor(SimpleXMLElement $xml, SimpleXMLElement $entry, Item $item);

    /**
     * Find the item URL.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     */
    abstract public function findItemUrl(SimpleXMLElement $entry, Item $item);

    /**
     * Find the item title.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     */
    abstract public function findItemTitle(SimpleXMLElement $entry, Item $item);

    /**
     * Genereate the item id.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     * @param \PicoFeed\Parser\Feed $feed  Feed object
     */
    abstract public function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item date.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param Item                  $item  Item object
     * @param \PicoFeed\Parser\Feed $feed  Feed object
     */
    abstract public function findItemDate(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item content.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     */
    abstract public function findItemContent(SimpleXMLElement $entry, Item $item);

    /**
     * Find the item enclosure.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     * @param \PicoFeed\Parser\Feed $feed  Feed object
     */
    abstract public function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item language.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param \PicoFeed\Parser\Item $item  Item object
     * @param \PicoFeed\Parser\Feed $feed  Feed object
     */
    abstract public function findItemLanguage(SimpleXMLElement $entry, Item $item, Feed $feed);
}
