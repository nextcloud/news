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
        return $this->content_type;
    }

    /**
     * Get data URI (http://en.wikipedia.org/wiki/Data_URI_scheme)
     *
     * @access public
     * @return string
     */
    public function getDataUri()
    {
        return sprintf(
            'data:%s;base64,%s',
            $this->content_type,
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
     * @return string
     */
    public function find($website_link)
    {
        $website = new Url($website_link);

        $icons = $this->extract($this->download($website->getBaseUrl('/'))->getContent());
        $icons[] = $website->getBaseUrl('/favicon.ico');

        foreach ($icons as $icon_link) {

            $icon_link = $this->convertLink($website, new Url($icon_link));
            $resource = $this->download($icon_link);
            $this->content = $resource->getContent();
            $this->content_type = $resource->getContentType();

            if ($this->content !== '') {
                return $icon_link;
            }
        }

        return '';
    }

    /**
     * Convert icon links to absolute url
     *
     * @access public
     * @param  \PicoFeed\Client\Url      $website     Website url
     * @param  \PicoFeed\Client\Url      $icon        Icon url
     * @return string
     */
    public function convertLink(Url $website, Url $icon)
    {
        $base_url = '';

        if ($icon->isRelativeUrl()) {
            $base_url = $website->getBaseUrl();
        }
        else if ($icon->isProtocolRelative()) {
            $icon->setScheme($website->getScheme());
        }

        return $icon->getAbsoluteUrl($base_url);
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
