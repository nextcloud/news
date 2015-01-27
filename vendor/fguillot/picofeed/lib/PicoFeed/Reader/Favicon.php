<?php

namespace PicoFeed\Reader;

use DOMXpath;
use PicoFeed\Client\Client;
use PicoFeed\Client\ClientException;
use PicoFeed\Client\Url;
use PicoFeed\Config\Config;
use PicoFeed\Logging\Logger;
use PicoFeed\Parser\XmlParser;

/**
 * Favicon class
 *
 * https://en.wikipedia.org/wiki/Favicon
 *
 * @author  Frederic Guillot
 * @package Reader
 */
class Favicon
{
    /**
     * Valid types for favicon (supported by browsers)
     *
     * @access private
     * @var array
     */
    private $types = array(
        'image/png',
        'image/gif',
        'image/x-icon',
        'image/jpeg',
        'image/jpg',
    );

    /**
     * Config class instance
     *
     * @access private
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * Icon binary content
     *
     * @access private
     * @var string
     */
    private $content = '';

    /**
     * Icon content type
     *
     * @access private
     * @var string
     */
    private $content_type = '';

    /**
     * Constructor
     *
     * @access public
     * @param  \PicoFeed\Config\Config   $config   Config class instance
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: new Config;
    }

    /**
     * Get the icon file content (available only after the download)
     *
     * @access public
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the icon file type (available only after the download)
     *
     * @access public
     * @return string
     */
    public function getType()
    {
        foreach ($this->types as $type) {
            if (strpos($this->content_type, $type) === 0) {
                return $type;
            }
        }

        return 'image/x-icon';
    }

    /**
     * Get data URI (http://en.wikipedia.org/wiki/Data_URI_scheme)
     *
     * @access public
     * @return string
     */
    public function getDataUri()
    {
        if (empty($this->content)) {
            return '';
        }

        return sprintf(
            'data:%s;base64,%s',
            $this->getType(),
            base64_encode($this->content)
        );
    }

    /**
     * Download and check if a resource exists
     *
     * @access public
     * @param  string               $url    URL
     * @return \PicoFeed\Client             Client instance
     */
    public function download($url)
    {
        $client = Client::getInstance();
        $client->setConfig($this->config);

        Logger::setMessage(get_called_class().' Download => '.$url);

        try {
            $client->execute($url);
        }
        catch (ClientException $e) {
            Logger::setMessage(get_called_class().' Download Failed => '.$e->getMessage());
        }

        return $client;
    }

    /**
     * Check if a remote file exists
     *
     * @access public
     * @param  string    $url    URL
     * @return boolean
     */
    public function exists($url)
    {
        return $this->download($url)->getContent() !== '';
    }

    /**
     * Get the icon link for a website
     *
     * @access public
     * @param  string    $website_link    URL
     * @param  string    $favicon_link    optional URL
     * @return string
     */
    public function find($website_link, $favicon_link = '')
    {
        $website = new Url($website_link);

        if ($favicon_link !== '') {
            $icons = array($favicon_link);
        } else {
            $icons = $this->extract($this->download($website->getBaseUrl('/'))->getContent());
            $icons[] = $website->getBaseUrl('/favicon.ico');
        }

        foreach ($icons as $icon_link) {
            $icon_link = Url::resolve($icon_link, $website);
            $resource = $this->download($icon_link);
            $this->content = $resource->getContent();
            $this->content_type = $resource->getContentType();

            if ($this->content !== '') {
                return $icon_link;
            } elseif ($favicon_link !== '') {
                return $this->find($website_link);
            }
        }

        return '';
    }

    /**
     * Extract the icon links from the HTML
     *
     * @access public
     * @param  string     $html     HTML
     * @return array
     */
    public function extract($html)
    {
        $icons = array();

        if (empty($html)) {
            return $icons;
        }

        $dom = XmlParser::getHtmlDocument($html);

        $xpath = new DOMXpath($dom);
        $elements = $xpath->query("//link[contains(@rel, 'icon') and not(contains(@rel, 'apple'))]");

        for ($i = 0; $i < $elements->length; $i++) {
            $icons[] = $elements->item($i)->getAttribute('href');
        }

        return $icons;
    }
}
