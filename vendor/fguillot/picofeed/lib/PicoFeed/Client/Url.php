<?php

namespace PicoFeed\Client;

/**
 * URL class
 *
 * @author  Frederic Guillot
 * @package Client
 */
class Url
{
    /**
     * URL
     *
     * @access private
     * @var string
     */
    private $url = '';

    /**
     * URL components
     *
     * @access private
     * @var array
     */
    private $components = array();

    /**
     * Constructor
     *
     * @access public
     * @param  string   $url    URL
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->components = parse_url($url) ?: array();

        // Issue with PHP < 5.4.7 and protocol relative url
        if (version_compare(PHP_VERSION, '5.4.7', '<') && $this->isProtocolRelative()) {
            $pos = strpos($this->components['path'], '/', 2);

            if ($pos === false) {
                $pos = strlen($this->components['path']);
            }

            $this->components['host'] = substr($this->components['path'], 2, $pos - 2);
            $this->components['path'] = substr($this->components['path'], $pos);
        }
    }

    /**
     * Shortcut method to get an absolute url from relative url
     *
     * @static
     * @access public
     * @param  mixed    $item_url      Unknown url (can be relative or not)
     * @param  mixed    $website_url   Website url
     * @return string
     */
    public static function resolve($item_url, $website_url)
    {
        $link = is_string($item_url) ? new Url($item_url) : $item_url;
        $website = is_string($website_url) ? new Url($website_url) : $website_url;

        if ($link->isRelativeUrl()) {

            if ($link->isRelativePath()) {
                return $link->getAbsoluteUrl($website->getBaseUrl($website->getBasePath()));
            }

            return $link->getAbsoluteUrl($website->getBaseUrl());
        }
        else if ($link->isProtocolRelative()) {
            $link->setScheme($website->getScheme());
        }

        return $link->getAbsoluteUrl();
    }

    /**
     * Shortcut method to get a base url
     *
     * @static
     * @access public
     * @param  string   $url
     * @return string
     */
    public static function base($url)
    {
        $link = new Url($url);
        return $link->getBaseUrl();
    }

    /**
     * Get the base URL
     *
     * @access public
     * @param  string   $suffix    Add a suffix to the url
     * @return string
     */
    public function getBaseUrl($suffix = '')
    {
        return $this->hasHost() ? $this->getScheme('://').$this->getHost().$this->getPort(':').$suffix : '';
    }

    /**
     * Get the absolute URL
     *
     * @access public
     * @param  string   $base_url    Use this url as base url
     * @return string
     */
    public function getAbsoluteUrl($base_url = '')
    {
        if ($base_url) {
            $base = new Url($base_url);
            $url = $base->getAbsoluteUrl().substr($this->getFullPath(), 1);
        }
        else {
            $url = $this->hasHost() ? $this->getBaseUrl().$this->getFullPath() : '';
        }

        return $url;
    }

    /**
     * Return true if the url is relative
     *
     * @access public
     * @return boolean
     */
    public function isRelativeUrl()
    {
        return ! $this->hasScheme() && ! $this->isProtocolRelative();
    }

    /**
     * Return true if the path is relative
     *
     * @access public
     * @return boolean
     */
    public function isRelativePath()
    {
        $path = $this->getPath();
        return empty($path) || $path{0} !== '/';
    }

    /**
     * Get the path
     *
     * @access public
     * @return string
     */
    public function getPath()
    {
        return empty($this->components['path']) ? '' : $this->components['path'];
    }

    /**
     * Get the base path
     *
     * @access public
     * @return string
     */
    public function getBasePath()
    {
        $current_path = $this->getPath();

        $path = $this->isRelativePath() ? '/' : '';
        $path .= substr($current_path, -1) === '/' ? $current_path : dirname($current_path);

        return preg_replace('/\\\\\/|\/\//', '/', $path.'/');
    }

    /**
     * Get the full path (path + querystring + fragment)
     *
     * @access public
     * @return string
     */
    public function getFullPath()
    {
        $path = $this->isRelativePath() ? '/' : '';
        $path .= $this->getPath();
        $path .= empty($this->components['query']) ? '' : '?'.$this->components['query'];
        $path .= empty($this->components['fragment']) ? '' : '#'.$this->components['fragment'];

        return $path;
    }

    /**
     * Get the hostname
     *
     * @access public
     * @return string
     */
    public function getHost()
    {
        return empty($this->components['host']) ? '' : $this->components['host'];
    }

    /**
     * Return true if the url has a hostname
     *
     * @access public
     * @return boolean
     */
    public function hasHost()
    {
        return ! empty($this->components['host']);
    }

    /**
     * Get the scheme
     *
     * @access public
     * @param  string    $suffix   Suffix to add when there is a scheme
     * @return string
     */
    public function getScheme($suffix = '')
    {
        return ($this->hasScheme() ? $this->components['scheme'] : 'http').$suffix;
    }

    /**
     * Set the scheme
     *
     * @access public
     * @param  string    $scheme    Set a scheme
     * @return string
     */
    public function setScheme($scheme)
    {
        $this->components['scheme'] = $scheme;
    }

    /**
     * Return true if the url has a scheme
     *
     * @access public
     * @return boolean
     */
    public function hasScheme()
    {
        return ! empty($this->components['scheme']);
    }

    /**
     * Get the port
     *
     * @access public
     * @param  string    $prefix   Prefix to add when there is a port
     * @return string
     */
    public function getPort($prefix = '')
    {
        return $this->hasPort() ? $prefix.$this->components['port'] : '';
    }

    /**
     * Return true if the url has a port
     *
     * @access public
     * @return boolean
     */
    public function hasPort()
    {
        return ! empty($this->components['port']);
    }

    /**
     * Return true if the url is protocol relative (start with //)
     *
     * @access public
     * @return boolean
     */
    public function isProtocolRelative()
    {
        return strpos($this->url, '//') === 0;
    }
}
