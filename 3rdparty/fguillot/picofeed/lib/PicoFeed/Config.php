<?php

namespace PicoFeed;

/**
 * Config class
 *
 * @author  Frederic Guillot
 * @package picofeed
 *
 * @method  \PicoFeed\Config setClientTimeout(integer $value)
 * @method  \PicoFeed\Config setClientUserAgent(string $value)
 * @method  \PicoFeed\Config setMaxRedirections(integer $value)
 * @method  \PicoFeed\Config setMaxBodySize(integer $value)
 * @method  \PicoFeed\Config setProxyHostname(string $value)
 * @method  \PicoFeed\Config setProxyPort(integer $value)
 * @method  \PicoFeed\Config setProxyUsername(string $value)
 * @method  \PicoFeed\Config setProxyPassword(string $value)
 * @method  \PicoFeed\Config setGrabberTimeout(integer $value)
 * @method  \PicoFeed\Config setGrabberUserAgent(string $value)
 * @method  \PicoFeed\Config setParserHashAlgo(string $value)
 * @method  \PicoFeed\Config setContentFiltering(boolean $value)
 * @method  \PicoFeed\Config setTimezone(string $value)
 * @method  \PicoFeed\Config setFilterIframeWhitelist(array $value)
 * @method  \PicoFeed\Config setFilterIntegerAttributes(array $value)
 * @method  \PicoFeed\Config setFilterAttributeOverrides(array $value)
 * @method  \PicoFeed\Config setFilterRequiredAttributes(array $value)
 * @method  \PicoFeed\Config setFilterMediaBlacklist(array $value)
 * @method  \PicoFeed\Config setFilterMediaAttributes(array $value)
 * @method  \PicoFeed\Config setFilterSchemeWhitelist(array $value)
 * @method  \PicoFeed\Config setFilterWhitelistedTags(array $value)
 * @method  \PicoFeed\Config setFilterBlacklistedTags(array $value)
 *
 * @method  integer    getClientTimeout()
 * @method  string     getClientUserAgent()
 * @method  integer    getMaxRedirections()
 * @method  integer    getMaxBodySize()
 * @method  string     getProxyHostname()
 * @method  integer    getProxyPort()
 * @method  string     getProxyUsername()
 * @method  string     getProxyPassword()
 * @method  integer    getGrabberTimeout()
 * @method  string     getGrabberUserAgent()
 * @method  string     getParserHashAlgo()
 * @method  boolean    getContentFiltering(bool $default_value)
 * @method  string     getTimezone()
 * @method  array      getFilterIframeWhitelist(array $default_value)
 * @method  array      getFilterIntegerAttributes(array $default_value)
 * @method  array      getFilterAttributeOverrides(array $default_value)
 * @method  array      getFilterRequiredAttributes(array $default_value)
 * @method  array      getFilterMediaBlacklist(array $default_value)
 * @method  array      getFilterMediaAttributes(array $default_value)
 * @method  array      getFilterSchemeWhitelist(array $default_value)
 * @method  array      getFilterWhitelistedTags(array $default_value)
 * @method  array      getFilterBlacklistedTags(array $default_value)
 */
class Config
{
    /**
     * Contains all parameters
     *
     * @access private
     * @var array
     */
    private $container = array();

    /**
     * Magic method to have any kind of setters or getters
     *
     * @access public
     * @param  string   $name        Getter/Setter name
     * @param  array    $arguments   Method arguments
     * @return mixed
     */
    public function __call($name , array $arguments)
    {
        $name = strtolower($name);
        $prefix = substr($name, 0, 3);
        $parameter = substr($name, 3);

        if ($prefix === 'set' && isset($arguments[0])) {
            $this->container[$parameter] = $arguments[0];
            return $this;
        }
        else if ($prefix === 'get') {
            $default_value = isset($arguments[0]) ? $arguments[0] : null;
            return isset($this->container[$parameter]) ? $this->container[$parameter] : $default_value;
        }
    }
}
