<?php

namespace PicoFeed;

use DateTime;
use DateTimeZone;

/**
 * Base parser class
 *
 * @author  Frederic Guillot
 * @package parser
 */
abstract class Parser
{
    /**
     * Config object
     *
     * @access private
     * @var \PicoFeed\Config
     */
    private $config = null;

    /**
     * Hash algorithm used to generate item id, any value supported by PHP, see hash_algos()
     *
     * @access private
     * @var string
     */
    private $hash_algo = 'crc32b'; // crc32b seems to be faster and shorter than other hash algorithms

    /**
     * Timezone used to parse feed dates
     *
     * @access private
     * @var string
     */
    private $timezone = 'UTC';

    /**
     * Feed content (XML data)
     *
     * @access protected
     * @var string
     */
    protected $content = '';

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
     * @param  string  $content        Feed content
     * @param  string  $http_encoding  HTTP encoding (headers)
     */
    public function __construct($content, $http_encoding = '')
    {
        $xml_encoding = XmlParser::getEncodingFromXmlTag($content);

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($content);

        // Encode everything in UTF-8
        Logging::setMessage(get_called_class().': HTTP Encoding "'.$http_encoding.'" ; XML Encoding "'.$xml_encoding.'"');
        $this->content = Encoding::convert($this->content, $xml_encoding ?: $http_encoding);

        // Workarounds
        $this->content = Filter::normalizeData($this->content);
    }

    /**
     * Parse the document
     *
     * @access public
     * @return mixed   \PicoFeed\Feed instance or false
     */
    public function execute()
    {
        Logging::setMessage(get_called_class().': begin parsing');

        $xml = XmlParser::getSimpleXml($this->content);

        if ($xml === false) {
            Logging::setMessage(get_called_class().': XML parsing error');
            Logging::setMessage(XmlParser::getErrors());
            return false;
        }

        $this->namespaces = $xml->getNamespaces(true);

        $feed = new Feed;
        $this->findFeedUrl($xml, $feed);
        $this->findFeedTitle($xml, $feed);
        $this->findFeedDescription($xml, $feed);
        $this->findFeedLanguage($xml, $feed);
        $this->findFeedId($xml, $feed);
        $this->findFeedDate($xml, $feed);
        $this->findFeedLogo($xml, $feed);

        foreach ($this->getItemsTree($xml) as $entry) {

            $item = new Item;
            $this->findItemAuthor($xml, $entry, $item);
            $this->findItemUrl($entry, $item);
            $this->findItemTitle($entry, $item);
            $this->findItemId($entry, $item, $feed);
            $this->findItemDate($entry, $item);
            $this->findItemContent($entry, $item);
            $this->findItemEnclosure($entry, $item, $feed);
            $this->findItemLanguage($entry, $item, $feed);

            $this->scrapWebsite($item);
            $this->filterItemContent($feed, $item);

            $feed->items[] = $item;
        }

        Logging::setMessage(get_called_class().PHP_EOL.$feed);

        return $feed;
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

            $grabber = new Grabber($item->getUrl());
            $grabber->setConfig($this->config);
            $grabber->download();

            if ($grabber->parse()) {
                $item->content = $grabber->getContent() ?: $item->content;
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
            $filter = Filter::html($item->getContent(), $feed->getUrl());
            $filter->setConfig($this->config);
            $item->content = $filter->execute();
        }
        else {
            Logging::setMessage(get_called_class().': Content filtering disabled');
        }
    }

    /**
     * Generate a unique id for an entry (hash all arguments)
     *
     * @access public
     * @param  string  $args  Pieces of data to hash
     * @return string         Id
     */
    public function generateId()
    {
        return hash($this->hash_algo, implode(func_get_args()));
    }

    /**
     * Try to parse all date format for broken feeds
     *
     * @access public
     * @param  string  $value  Original date format
     * @return integer         Timestamp
     */
    public function parseDate($value)
    {
        // Format => truncate to this length if not null
        $formats = array(
            DATE_ATOM => null,
            DATE_RSS => null,
            DATE_COOKIE => null,
            DATE_ISO8601 => null,
            DATE_RFC822 => null,
            DATE_RFC850 => null,
            DATE_RFC1036 => null,
            DATE_RFC1123 => null,
            DATE_RFC2822 => null,
            DATE_RFC3339 => null,
            'D, d M Y H:i:s' => 25,
            'D, d M Y h:i:s' => 25,
            'D M d Y H:i:s' => 24,
            'Y-m-d H:i:s' => 19,
            'Y-m-d\TH:i:s' => 19,
            'd/m/Y H:i:s' => 19,
            'D, d M Y' => 16,
            'Y-m-d' => 10,
            'd-m-Y' => 10,
            'm-d-Y' => 10,
            'd.m.Y' => 10,
            'm.d.Y' => 10,
            'd/m/Y' => 10,
            'm/d/Y' => 10,
        );

        $value = trim($value);

        foreach ($formats as $format => $length) {

            $truncated_value = $value;
            if ($length !== null) {
                $truncated_value = substr($truncated_value, 0, $length);
            }

            $timestamp = $this->getValidDate($format, $truncated_value);
            if ($timestamp > 0) {
                return $timestamp;
            }
        }

        $date = new DateTime('now', new DateTimeZone($this->timezone));
        return $date->getTimestamp();
    }

    /**
     * Get a valid date from a given format
     *
     * @access public
     * @param  string  $format   Date format
     * @param  string  $value    Original date value
     * @return integer           Timestamp
     */
    public function getValidDate($format, $value)
    {
        $date = DateTime::createFromFormat($format, $value, new DateTimeZone($this->timezone));

        if ($date !== false) {

            $errors = DateTime::getLastErrors();

            if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) {
                return $date->getTimestamp();
            }
        }

        return 0;
    }

    /**
     * Hardcoded list of hostname/token to exclude from id generation
     *
     * @access public
     * @param  string  $url  URL
     * @return boolean
     */
    public function isExcludedFromId($url)
    {
        $exclude_list = array('ap.org', 'jacksonville.com');

        foreach ($exclude_list as $token) {
            if (strpos($url, $token) !== false) return true;
        }

        return false;
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
     * @return \PicoFeed\Parser
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
     * @return \PicoFeed\Parser
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone ?: $this->timezone;
        return $this;
    }

    /**
     * Set config object
     *
     * @access public
     * @param  \PicoFeed\Config  $config   Config instance
     * @return \PicoFeed\Parser
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
     * @return \PicoFeed\Parser
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
     * @return \PicoFeed\Parser
     */
    public function enableContentGrabber()
    {
        $this->enable_grabber = true;
    }

    /**
     * Set ignored URLs for the content grabber
     *
     * @access public
     * @param  array   $urls   URLs
     * @return \PicoFeed\Parser
     */
    public function setGrabberIgnoreUrls(array $urls)
    {
        $this->grabber_ignore_urls = $urls;
    }
}
