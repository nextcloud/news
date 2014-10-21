<?php

namespace PicoFeed;

use DOMXPath;
use PicoFeed\Config;
use PicoFeed\XmlParser;
use PicoFeed\Logging;
use PicoFeed\Filter;
use PicoFeed\Client;
use PicoFeed\Parser;
use PicoFeed\Url;

/**
 * Reader class
 *
 * @author  Frederic Guillot
 * @package picofeed
 */
class Reader
{
    /**
     * Feed or site URL
     *
     * @access private
     * @var string
     */
    private $url = '';

    /**
     * Feed content
     *
     * @access private
     * @var string
     */
    private $content = '';

    /**
     * HTTP encoding
     *
     * @access private
     * @var string
     */
    private $encoding = '';

    /**
     * Config class instance
     *
     * @access private
     * @var \PicoFeed\Config
     */
    private $config = null;

    /**
     * Constructor
     *
     * @access public
     * @param  \PicoFeed\Config   $config   Config class instance
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: new Config;
        Logging::setTimezone($this->config->getTimezone());
    }

    /**
     * Download a feed
     *
     * @access public
     * @param  string  $url            Feed content
     * @param  string  $last_modified  Last modified HTTP header
     * @param  string  $etag           Etag HTTP header
     * @return \PicoFeed\Client
     */
    public function download($url, $last_modified = '', $etag = '')
    {
        if (strpos($url, 'http') !== 0) {
            $url = 'http://'.$url;
        }

        $client = Client::getInstance();
        $client->setConfig($this->config)
               ->setLastModified($last_modified)
               ->setEtag($etag);

        if ($client->execute($url)) {
            $this->content = $client->getContent();
            $this->url = $client->getUrl();
            $this->encoding = $client->getEncoding();
        }

        return $client;
    }

    /**
     * Get a parser instance with a custom config
     *
     * @access public
     * @param  string  $name  Parser name
     * @return \PicoFeed\Parser
     */
    public function getParserInstance($name)
    {
        require_once __DIR__.'/Parsers/'.ucfirst($name).'.php';
        $name = '\PicoFeed\Parsers\\'.$name;

        $parser = new $name($this->content, $this->encoding);
        $parser->setHashAlgo($this->config->getParserHashAlgo());
        $parser->setTimezone($this->config->getTimezone());
        $parser->setConfig($this->config);

        return $parser;
    }

    /**
     * Get the first XML tag
     *
     * @access public
     * @param  string  $data  Feed content
     * @return string
     */
    public function getFirstTag($data)
    {
        // Strip HTML comments (max of 5,000 characters long to prevent crashing)
        $data = preg_replace('/<!--(.{0,5000}?)-->/Uis', '', $data);

        /* Strip Doctype:
         * Doctype needs to be within the first 100 characters. (Ideally the first!)
         * If it's not found by then, we need to stop looking to prevent PREG
         * from reaching max backtrack depth and crashing.
         */
        $data = preg_replace('/^.{0,100}<!DOCTYPE([^>]*)>/Uis', '', $data);

        // Strip <?xml version....
        $data = Filter::stripXmlTag($data);

        // Find the first tag
        $open_tag = strpos($data, '<');
        $close_tag = strpos($data, '>');

        return substr($data, $open_tag, $close_tag);
    }

    /**
     * Detect the feed format
     *
     * @access public
     * @param  string    $parser_name   Parser name
     * @param  string    $haystack      First XML tag
     * @param  array     $needles       List of strings that need to be there
     * @return mixed                    False on failure or Parser instance
     */
    public function detectFormat($parser_name, $haystack, array $needles)
    {
        $results = array();

        foreach ($needles as $needle) {
            $results[] = strpos($haystack, $needle) !== false;
        }

        if (! in_array(false, $results, true)) {
            Logging::setMessage(get_called_class().': Format detected => '.$parser_name);
            return $this->getParserInstance($parser_name);
        }

        return false;
    }

    /**
     * Discover feed format and return a parser instance
     *
     * @access public
     * @param  boolean  $discover      Enable feed autodiscovery in HTML document
     * @return mixed                   False on failure or Parser instance
     */
    public function getParser($discover = false)
    {
        $formats = array(
            array('parser' => 'Atom', 'needles' => array('<feed')),
            array('parser' => 'Rss20', 'needles' => array('<rss', '2.0')),
            array('parser' => 'Rss92', 'needles' => array('<rss', '0.92')),
            array('parser' => 'Rss91', 'needles' => array('<rss', '0.91')),
            array('parser' => 'Rss10', 'needles' => array('<rdf:'/*, 'xmlns="http://purl.org/rss/1.0/"'*/)),
        );

        $first_tag = $this->getFirstTag($this->content);

        foreach ($formats as $format) {

            $parser = $this->detectFormat($format['parser'], $first_tag, $format['needles']);

            if ($parser !== false) {
                return $parser;
            }
        }

        if ($discover === true) {

            Logging::setMessage(get_called_class().': Format not supported or feed malformed');
            Logging::setMessage(get_called_class().': Content => '.PHP_EOL.$this->content);

            return false;
        }
        else if ($this->discover()) {
            return $this->getParser(true);
        }

        Logging::setMessage(get_called_class().': Subscription not found');
        Logging::setMessage(get_called_class().': Content => '.PHP_EOL.$this->content);

        return false;
    }

    /**
     * Discover the feed url inside a HTML document and download the feed
     *
     * @access public
     * @return boolean
     */
    public function discover()
    {
        if (! $this->content) {
            return false;
        }

        Logging::setMessage(get_called_class().': Try to discover a subscription');

        $dom = XmlParser::getHtmlDocument($this->content);
        $xpath = new DOMXPath($dom);

        $queries = array(
            '//link[@type="application/rss+xml"]',
            '//link[@type="application/atom+xml"]',
        );

        foreach ($queries as $query) {

            $nodes = $xpath->query($query);

            if ($nodes->length !== 0) {

                $link = $nodes->item(0)->getAttribute('href');

                if (! empty($link)) {

                    $feedUrl = new Url($link);
                    $siteUrl = new Url($this->url);

                    $link = $feedUrl->getAbsoluteUrl($feedUrl->isRelativeUrl() ? $siteUrl->getBaseUrl() : '');

                    Logging::setMessage(get_called_class().': Find subscription link: '.$link);

                    $this->download($link);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the downloaded content
     *
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the page content
     *
     * @access public
     * @param  string  $content   Page content
     * @return \PicoFeed\Reader
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get final URL
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL
     *
     * @access public
     * @param  string  $url   URL
     * @return \PicoFeed\Reader
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}
