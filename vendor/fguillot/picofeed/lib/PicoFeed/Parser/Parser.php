<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;
use PicoFeed\Client\Url;
use PicoFeed\Encoding\Encoding;
use PicoFeed\Filter\Filter;
use PicoFeed\Logging\Logger;
use PicoFeed\Scraper\Scraper;

/**
 * Base parser class
 *
 * @author  Frederic Guillot
 * @package Parser
 */
abstract class Parser
{
    /**
     * Config object
     *
     * @access private
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * DateParser object
     *
     * @access protected
     * @var \PicoFeed\Parser\DateParser
     */
    protected $date;

    /**
     * Hash algorithm used to generate item id, any value supported by PHP, see hash_algos()
     *
     * @access private
     * @var string
     */
    private $hash_algo = 'sha256';

    /**
     * Feed content (XML data)
     *
     * @access protected
     * @var string
     */
    protected $content = '';

    /**
     * Fallback url
     *
     * @access protected
     * @var string
     */
    protected $fallback_url = '';

    /**
     * XML namespaces
     *
     * @access protected
     * @var array
     */
    protected $namespaces = array();

    /**
     * Enable the content filtering
     *
     * @access private
     * @var bool
     */
    private $enable_filter = true;

    /**
     * Enable the content grabber
     *
     * @access private
     * @var bool
     */
    private $enable_grabber = false;

    /**
     * Enable the content grabber on all pages
     *
     * @access private
     * @var bool
     */
    private $grabber_needs_rule_file = false;

    /**
     * Ignore those urls for the content scraper
     *
     * @access private
     * @var array
     */
    private $grabber_ignore_urls = array();

    /**
     * Constructor
     *
     * @access public
     * @param  string  $content          Feed content
     * @param  string  $http_encoding    HTTP encoding (headers)
     * @param  string  $fallback_url     Fallback url when the feed provide relative or broken url
     */
    public function __construct($content, $http_encoding = '', $fallback_url = '')
    {
        $this->date = new DateParser;
        $this->fallback_url = $fallback_url;
        $xml_encoding = XmlParser::getEncodingFromXmlTag($content);

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($content);

        // Encode everything in UTF-8
        Logger::setMessage(get_called_class().': HTTP Encoding "'.$http_encoding.'" ; XML Encoding "'.$xml_encoding.'"');
        $this->content = Encoding::convert($this->content, $xml_encoding ?: $http_encoding);

        // Workarounds
        $this->content = Filter::normalizeData($this->content);
    }

    /**
     * Parse the document
     *
     * @access public
     * @return \PicoFeed\Parser\Feed
     */
    public function execute()
    {
        Logger::setMessage(get_called_class().': begin parsing');

        $xml = XmlParser::getSimpleXml($this->content);

        if ($xml === false) {
            Logger::setMessage(get_called_class().': XML parsing error');
            Logger::setMessage(XmlParser::getErrors());
            throw new MalformedXmlException('XML parsing error');
        }

        $this->namespaces = $xml->getNamespaces(true);

        $feed = new Feed;

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

            $item = new Item;
            $item->xml = $entry;
            $item->namespaces = $this->namespaces;

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
     * Check if the feed url is correct
     *
     * @access public
     * @param  Feed    $feed          Feed object
     */
    public function checkFeedUrl(Feed $feed)
    {
        if ($feed->getFeedUrl() === '') {
            $feed->feed_url = $this->fallback_url;
        }
        else {
            $feed->feed_url = Url::resolve($feed->getFeedUrl(), $this->fallback_url);
        }
    }

    /**
     * Check if the site url is correct
     *
     * @access public
     * @param  Feed    $feed          Feed object
     */
    public function checkSiteUrl(Feed $feed)
    {
        if ($feed->getSiteUrl() === '') {
            $feed->site_url = Url::base($feed->getFeedUrl());
        }
        else {
            $feed->site_url = Url::resolve($feed->getSiteUrl(), $this->fallback_url);
        }
    }

    /**
     * Check if the item url is correct
     *
     * @access public
     * @param  Feed    $feed          Feed object
     * @param  Item    $item          Item object
     */
    public function checkItemUrl(Feed $feed, Item $item)
    {
        $item->url = Url::resolve($item->getUrl(), $feed->getSiteUrl());
    }

    /**
     * Fetch item content with the content grabber
     *
     * @access public
     * @param  Item    $item          Item object
     */
    public function scrapWebsite(Item $item)
    {
        if ($this->enable_grabber && ! in_array($item->getUrl(), $this->grabber_ignore_urls)) {

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
     * Filter HTML for entry content
     *
     * @access public
     * @param  Feed    $feed          Feed object
     * @param  Item    $item          Item object
     */
    public function filterItemContent(Feed $feed, Item $item)
    {
        if ($this->isFilteringEnabled()) {
            $filter = Filter::html($item->getContent(), $feed->getSiteUrl());
            $filter->setConfig($this->config);
            $item->content = $filter->execute();
        }
        else {
            Logger::setMessage(get_called_class().': Content filtering disabled');
        }
    }

    /**
     * Generate a unique id for an entry (hash all arguments)
     *
     * @access public
     * @return string
     */
    public function generateId()
    {
        return hash($this->hash_algo, implode(func_get_args()));
    }

    /**
     * Return true if the given language is "Right to Left"
     *
     * @static
     * @access public
     * @param  string  $language  Language: fr-FR, en-US
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
     * Set Hash algorithm used for id generation
     *
     * @access public
     * @param  string   $algo   Algorithm name
     * @return \PicoFeed\Parser\Parser
     */
    public function setHashAlgo($algo)
    {
        $this->hash_algo = $algo ?: $this->hash_algo;
        return $this;
    }

    /**
     * Set a different timezone
     *
     * @see    http://php.net/manual/en/timezones.php
     * @access public
     * @param  string   $timezone   Timezone
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
     * Set config object
     *
     * @access public
     * @param  \PicoFeed\Config\Config  $config   Config instance
     * @return \PicoFeed\Parser\Parser
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Enable the content grabber
     *
     * @access public
     * @return \PicoFeed\Parser\Parser
     */
    public function disableContentFiltering()
    {
        $this->enable_filter = false;
    }

    /**
     * Return true if the content filtering is enabled
     *
     * @access public
     * @return boolean
     */
    public function isFilteringEnabled()
    {
        if ($this->config === null) {
            return $this->enable_filter;
        }

        return $this->config->getContentFiltering($this->enable_filter);
    }

    /**
     * Enable the content grabber
     *
     * @access public
     * @param bool $needs_rule_file true if only pages with rule files should be
     * scraped
     * @return \PicoFeed\Parser\Parser
     */
    public function enableContentGrabber($needs_rule_file = false)
    {
        $this->enable_grabber = true;
        $this->grabber_needs_rule_file = $needs_rule_file;
    }

    /**
     * Set ignored URLs for the content grabber
     *
     * @access public
     * @param  array   $urls   URLs
     * @return \PicoFeed\Parser\Parser
     */
    public function setGrabberIgnoreUrls(array $urls)
    {
        $this->grabber_ignore_urls = $urls;
    }

    /**
     * Find the feed url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedUrl(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the site url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findSiteUrl(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed title
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedTitle(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed description
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedDescription(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed language
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedLanguage(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed id
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedId(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed date
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedDate(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed logo url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedLogo(SimpleXMLElement $xml, Feed $feed);

    /**
     * Find the feed icon
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findFeedIcon(SimpleXMLElement $xml, Feed $feed);

    /**
     * Get the path to the items XML tree
     *
     * @access public
     * @param  SimpleXMLElement   $xml   Feed xml
     * @return SimpleXMLElement
     */
    public abstract function getItemsTree(SimpleXMLElement $xml);

    /**
     * Find the item author
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public abstract function findItemAuthor(SimpleXMLElement $xml, SimpleXMLElement $entry, Item $item);

    /**
     * Find the item URL
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public abstract function findItemUrl(SimpleXMLElement $entry, Item $item);

    /**
     * Find the item title
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public abstract function findItemTitle(SimpleXMLElement $entry, Item $item);

    /**
     * Genereate the item id
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item date
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  Item                      $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findItemDate(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item content
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public abstract function findItemContent(SimpleXMLElement $entry, Item $item);

    /**
     * Find the item enclosure
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed);

    /**
     * Find the item language
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public abstract function findItemLanguage(SimpleXMLElement $entry, Item $item, Feed $feed);
}
