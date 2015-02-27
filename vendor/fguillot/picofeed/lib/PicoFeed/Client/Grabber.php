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
     * Flag to skip download and parsing
     *
     * @access private
     * @var boolean
     */
    private $skip_processing = false;

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
        'entry-body',
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
        'post_title',
        'by_line',
        'byline',
        'sponsors',
    );

    /**
     * Tags to remove
     *
     * @access private
     * @var array
     */
    private $stripTags = array(
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

        $this->handleFiles();
        $this->handleStreamingVideos();
    }

    /**
     * Set config object
     *
     * @access public
     * @param  \PicoFeed\Config\Config   $config    Config instance
     * @return Grabber
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get URL to download.
     *
     * @access  public
     * @return  string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set URL to download and reset object to use for another grab.
     *
     * @access  public
     * @param   string  $url    URL
     * @return  string
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->html = "";
        $this->content = "";
        $this->encoding = "";

        $this->handleFiles();
        $this->handleStreamingVideos();
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
     * Get filtered relevant content
     *
     * @access public
     * @return string
     */
    public function getFilteredContent()
    {
        $filter = Filter::html($this->content, $this->url);
        $filter->setConfig($this->config);
        return $filter->execute();
    }

    /**
     * Return the Youtube embed player and skip processing
     *
     * @access public
     * @return string
     */
    public function handleStreamingVideos()
    {
        if (preg_match("#(?<=v=|v\/|vi=|vi\/|youtu.be\/)[a-zA-Z0-9_-]{11}#", $this->url, $matches)) {
            $this->content = '<iframe width="560" height="315" src="//www.youtube.com/embed/'.$matches[0].'" frameborder="0"></iframe>';
            $this->skip_processing = true;
        }
    }

    /**
     * Skip processing for PDF documents
     *
     * @access public
     * @return string
     */
    public function handleFiles()
    {
        if (substr($this->url, -3) === 'pdf') {
            $this->skip_processing = true;
            Logger::setMessage(get_called_class().': PDF document => processing skipped');
        }
    }

    /**
     * Parse the HTML content
     *
     * @access public
     * @return bool
     */
    public function parse()
    {
        if ($this->skip_processing) {
            return true;
        }

        if ($this->html) {
            $html_encoding = XmlParser::getEncodingFromMetaTag($this->html);

            // Encode everything in UTF-8
            Logger::setMessage(get_called_class().': HTTP Encoding "'.$this->encoding.'" ; HTML Encoding "'.$html_encoding.'"');
            $this->html = Encoding::convert($this->html, $html_encoding ?: $this->encoding);
            $this->html = Filter::stripHeadTags($this->html);

            Logger::setMessage(get_called_class().': Content length: '.strlen($this->html).' bytes');
            $rules = $this->getRules();

            if (is_array($rules)) {
                Logger::setMessage(get_called_class().': Parse content with rules');
                $this->parseContentWithRules($rules);
            }
            else {
                Logger::setMessage(get_called_class().': Parse content with candidates');
                $this->parseContentWithCandidates();
            }
        }
        else {
            Logger::setMessage(get_called_class().': No content fetched');
        }

        Logger::setMessage(get_called_class().': Content length: '.strlen($this->content).' bytes');
        Logger::setMessage(get_called_class().': Grabber done');

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
        if (! $this->skip_processing && $this->url != '') {

            try {

                $client = Client::getInstance();
                $client->setConfig($this->config);
                $client->execute($this->url);

                $this->url = $client->getUrl();
                $this->html = $client->getContent();
                $this->encoding = $client->getEncoding();
            }
            catch (ClientException $e) {
                Logger::setMessage(get_called_class().': '.$e->getMessage());
            }
        }

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

            Logger::setMessage(get_called_class().': Try this candidate: "'.$candidate.'"');

            $nodes = $xpath->query('//*[(contains(@class, "'.$candidate.'") or @id="'.$candidate.'") and not (contains(@class, "nav") or contains(@class, "page"))]');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logger::setMessage(get_called_class().': Find candidate "'.$candidate.'" ('.strlen($this->content).' bytes)');
                break;
            }
        }

        // Try to fetch <article/>
        if (strlen($this->content) < 200) {

            $nodes = $xpath->query('//article');

            if ($nodes !== false && $nodes->length > 0) {
                $this->content = $dom->saveXML($nodes->item(0));
                Logger::setMessage(get_called_class().': Find <article/> tag ('.strlen($this->content).' bytes)');
            }
        }

        // Get everything
        if (strlen($this->content) < 50) {

            $nodes = $xpath->query('//body');

            if ($nodes !== false && $nodes->length > 0) {
                Logger::setMessage(get_called_class().' No enought content fetched, get //body');
                $this->content = $dom->saveXML($nodes->item(0));
            }
        }

        Logger::setMessage(get_called_class().': Strip garbage');
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
                    Logger::setMessage(get_called_class().': Strip tag: "'.$tag.'"');
                    foreach ($nodes as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }

            foreach ($this->stripAttributes as $attribute) {

                $nodes = $xpath->query('//*[contains(@class, "'.$attribute.'") or contains(@id, "'.$attribute.'")]');

                if ($nodes !== false && $nodes->length > 0) {
                    Logger::setMessage(get_called_class().': Strip attribute: "'.$attribute.'"');
                    foreach ($nodes as $node) {
                        if ($this->shouldRemove($dom, $node)) {
                            $node->parentNode->removeChild($node);
                        }
                    }
                }
            }

            $this->content = $dom->saveXML($dom->documentElement);
        }
    }

    /**
     * Return false if the node should not be removed
     *
     * @access public
     * @param  DomDocument  $dom
     * @param  DomNode      $node
     * @return boolean
     */
    public function shouldRemove($dom, $node)
    {
        $document_length = strlen($dom->textContent);
        $node_length = strlen($node->textContent);

        if ($document_length === 0) {
            return true;
        }

        $ratio = $node_length * 100 / $document_length;

        if ($ratio >= 90) {
            Logger::setMessage(get_called_class().': Should not remove this node ('.$node->nodeName.') ratio: '.$ratio.'%');
            return false;
        }

        return true;
    }
}
