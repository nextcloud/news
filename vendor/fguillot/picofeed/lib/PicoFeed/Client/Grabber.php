<?php

namespace PicoFeed\Client;

use DOMXPath;

use PicoFeed\Encoding\Encoding;
use PicoFeed\Logging\Logger;
use PicoFeed\Filter\Filter;
use PicoFeed\Parser\XmlParser;

/**
 * Grabber class
 *
 * @author  Frederic Guillot
 * @package Client
 */
class Grabber
{
    /**
     * URL
     *
     * @access private
     * @var string
     */
    private $url = '';

    /**
     * Relevant content
     *
     * @access private
     * @var string
     */
    private $content = '';

    /**
     * HTML content
     *
     * @access private
     * @var string
     */
    private $html = '';

    /**
     * HTML content encoding
     *
     * @access private
     * @var string
     */
    private $encoding = '';

    /**
     * List of attributes to try to get the content, order is important, generic terms at the end
     *
     * @access private
     * @var array
     */
    private $candidatesAttributes = array(
        'articleBody',
        'articlebody',
        'article-body',
        'articleContent',
        'articlecontent',
        'article-content',
        'articlePage',
        'post-content',
        'post_content',
        'entry-content',
        'main-content',
        'story_content',
        'storycontent',
        'entryBox',
        'entrytext',
        'comic',
        'post',
        'article',
        'content',
        'main',
    );

    /**
     * List of attributes to strip
     *
     * @access private
     * @var array
     */
    private $stripAttributes = array(
        'comment',
        'share',
        'links',
        'toolbar',
        'fb',
        'footer',
        'credit',
        'bottom',
        'nav',
        'header',
        'social',
        'tag',
        'metadata',
        'entry-utility',
        'related-posts',
        'tweet',
        'categories',
    );

    /**
     * Tags to remove
     *
     * @access private
     * @var array
     */
    private $stripTags = array(
        'script',
        'style',
        'nav',
        'header',
        'footer',
        'aside',
        'form',
    );

    /**
     * Config object
     *
     * @access private
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @access public
     * @param  string   $url       Url
     * @param  string   $html      HTML content
     * @param  string   $encoding  Charset
     */
    public function __construct($url, $html = '', $encoding = 'utf-8')
    {
        $this->url = $url;
        $this->html = $html;
        $this->encoding = $encoding;
    }

    /**
     * Set config object
     *
     * @access public
     * @param  \PicoFeed\Config\Config   $config    Config instance
     * @return \PicoFeed\Grabber
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get relevant content
     *
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get raw content (unfiltered)
     *
     * @access public
     * @return string
     */
    public function getRawContent()
    {
        return $this->html;
    }

    /**
     * Parse the HTML content
     *
     * @access public
     * @return bool
     */
    public function parse()
    {
        if ($this->html) {

            Logger::setMessage(get_called_class().' Fix encoding');
            Logger::setMessage(get_called_class().': HTTP Encoding "'.$this->encoding.'"');

            $this->html = Filter::stripHeadTags($this->html);
            $this->html = Encoding::convert($this->html, $this->encoding);

            Logger::setMessage(get_called_class().' Content length: '.strlen($this->html).' bytes');
            $rules = $this->getRules();

            if (is_array($rules)) {
                Logger::setMessage(get_called_class().' Parse content with rules');
                $this->parseContentWithRules($rules);
            }
            else {
                Logger::setMessage(get_called_class().' Parse content with candidates');
                $this->parseContentWithCandidates();
            }
        }
        else {
            Logger::setMessage(get_called_class().' No content fetched');
        }

        Logger::setMessage(get_called_class().' Content length: '.strlen($this->content).' bytes');
        Logger::setMessage(get_called_class().' Grabber done');

        return $this->content !== '';
    }

    /**
     * Download the HTML content
     *
     * @access public
     * @return HTML content
     */
    public function download()
    {
        $client = Client::getInstance();
        $client->setConfig($this->config);
        $client->execute($this->url);

        $this->html = $client->getContent();
        $this->encoding = $client->getEncoding();

        return $this->html;
    }

    /**
     * Try to find a predefined rule
     *
     * @access public
     * @return mixed
     */
    public function getRules()
    {
        $hostname = parse_url($this->url, PHP_URL_HOST);

        if ($hostname === false) {
            return false;
        }

        $files = array($hostname);

        if (substr($hostname, 0, 4) == 'www.') {
            $files[] = substr($hostname, 4);
        }

        if (($pos = strpos($hostname, '.')) !== false) {
            $files[] = substr($hostname, $pos);
            $files[] = substr($hostname, $pos + 1);
            $files[] = substr($hostname, 0, $pos);
        }

        foreach ($files as $file) {

            $filename = __DIR__.'/../Rules/'.$file.'.php';

            if (file_exists($filename)) {
                Logger::setMessage(get_called_class().' Load rule: '.$file);
                return include $filename;
            }
        }

        return false;
    }

    /**
     * Get the relevant content with predefined rules
     *
     * @access public
     * @param  array   $rules   Rules
     */
    public function parseContentWithRules(array $rules)
    {
        // Logger::setMessage($this->html);
        $dom = XmlParser::getHtmlDocument('<?xml version="1.0" encoding="UTF-8">'.$this->html);
        $xpath = new DOMXPath($dom);

        if (isset($rules['strip']) && is_array($rules['strip'])) {

            foreach ($rules['strip'] as $pattern) {

                $nodes = $xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }

        if (isset($rules['body']) && is_array($rules['body'])) {

            foreach ($rules['body'] as $pattern) {

                $nodes = $xpath->query($pattern);

                if ($nodes !== false && $nodes->length > 0) {
                    foreach ($nodes as $node) {
                        $this->content .= $dom->saveXML($node);
                    }
                }
            }
        }
    }

    /**
     * Get the relevant content with the list of potential attributes
     *
     * @access public
     */
    public function parseContentWithCandidates()
    {
        $dom = XmlParser::getHtmlDocument('<?xml version="1.0" encoding="UTF-8">'.$this->html);
        $xpath = new DOMXPath($dom);

        // Try to lookup in each tag
        foreach ($this->candidatesAttributes as $candidate) {

            Logger::setMessage(get_called_class().' Try this candidate: "'.$candidate.'"');

            $nodes = $xpath->query('//*[(contains(@class, "'.$candidate.'") or @id="'.$candidate.'") and not (contains(@class, "nav") or contains(@class, "page"))]');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logger::setMessage(get_called_class().' Find candidate "'.$candidate.'" ('.strlen($this->content).' bytes)');
                break;
            }
        }

        // Try to fetch <article/>
        if (! $this->content) {

            $nodes = $xpath->query('//article');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logger::setMessage(get_called_class().' Find <article/> tag ('.strlen($this->content).' bytes)');
            }
        }

        if (strlen($this->content) < 50) {
            Logger::setMessage(get_called_class().' No enought content fetched, get the full body');
            $this->content = $dom->saveXML($dom->firstChild);
        }

        Logger::setMessage(get_called_class().' Strip garbage');
        $this->stripGarbage();
    }

    /**
     * Strip useless tags
     *
     * @access public
     */
    public function stripGarbage()
    {
        $dom = XmlParser::getDomDocument($this->content);

        if ($dom !== false) {

            $xpath = new DOMXPath($dom);

            foreach ($this->stripTags as $tag) {

                $nodes = $xpath->query('//'.$tag);

                if ($nodes !== false && $nodes->length > 0) {
                    Logger::setMessage(get_called_class().' Strip tag: "'.$tag.'"');
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            foreach ($this->stripAttributes as $attribute) {

                $nodes = $xpath->query('//*[contains(@class, "'.$attribute.'") or contains(@id, "'.$attribute.'")]');

                if ($nodes !== false && $nodes->length > 0) {
                    Logger::setMessage(get_called_class().' Strip attribute: "'.$attribute.'"');
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            $this->content = $dom->saveXML($dom->documentElement);
        }
    }
}
