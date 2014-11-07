<?php

namespace PicoFeed\Client;

use DOMXpath;

use PicoFeed\Config\Config;
use PicoFeed\Logging\Logging;
use PicoFeed\Parser\XmlParser;

/**
 * Favicon class
 *
 * https://en.wikipedia.org/wiki/Favicon
 *
 * @author  Frederic Guillot
 * @package Client
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
     * Icon content
     *
     * @access private
     * @var string
     */
    private $content = '';

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
     * Download and check if a resource exists
     *
     * @access public
     * @param  string    $url    URL
     * @return string            Resource content
     */
    public function download($url)
    {
        try {

            Logging::setMessage(get_called_class().' Download => '.$url);

            $client = Client::getInstance();
            $client->setConfig($this->config);
            $client->execute($url);

            return $client->getContent();
        }
        catch (ClientException $e) {
            return '';
        }
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
        return $this->download($url) !== '';
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

        $icons = $this->extract($this->download($website->getBaseUrl('/')));
        $icons[] = $website->getBaseUrl('/favicon.ico');

        foreach ($icons as $icon_link) {

            $icon_link = $this->convertLink($website, new Url($icon_link));
            $this->content = $this->download($icon_link);

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
