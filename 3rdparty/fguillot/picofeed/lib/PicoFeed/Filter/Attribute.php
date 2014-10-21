<?php

namespace PicoFeed\Filter;

use \PicoFeed\Url;
use \PicoFeed\Filter;

/**
 * Attribute Filter class
 *
 * @author  Frederic Guillot
 * @package filter
 */
class Attribute
{
    /**
     * Tags and attribute whitelist
     *
     * @access private
     * @var array
     */
    private $attribute_whitelist = array(
        'audio' => array('controls', 'src'),
        'video' => array('poster', 'controls', 'height', 'width', 'src'),
        'source' => array('src', 'type'),
        'dt' => array(),
        'dd' => array(),
        'dl' => array(),
        'table' => array(),
        'caption' => array(),
        'tr' => array(),
        'th' => array(),
        'td' => array(),
        'tbody' => array(),
        'thead' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'strong' => array(),
        'em' => array(),
        'code' => array(),
        'pre' => array(),
        'blockquote' => array(),
        'p' => array(),
        'ul' => array(),
        'li' => array(),
        'ol' => array(),
        'br' => array(),
        'del' => array(),
        'a' => array('href'),
        'img' => array('src', 'title', 'alt'),
        'figure' => array(),
        'figcaption' => array(),
        'cite' => array(),
        'time' => array('datetime'),
        'abbr' => array('title'),
        'iframe' => array('width', 'height', 'frameborder', 'src'),
        'q' => array('cite')
    );

    /**
     * Scheme whitelist
     *
     * For a complete list go to http://en.wikipedia.org/wiki/URI_scheme
     *
     * @access private
     * @var array
     */
    private $scheme_whitelist = array(
        'bitcoin:',
        'callto:',
        'ed2k://',
        'facetime://',
        'feed:',
        'ftp://',
        'geo:',
        'git://',
        'http://',
        'https://',
        'irc://',
        'irc6://',
        'ircs://',
        'jabber:',
        'magnet:',
        'mailto:',
        'nntp://',
        'rtmp://',
        'sftp://',
        'sip:',
        'sips:',
        'skype:',
        'smb://',
        'sms:',
        'spotify:',
        'ssh:',
        'steam:',
        'svn://',
        'tel:',
    );

    /**
     * Iframe source whitelist, everything else is ignored
     *
     * @access private
     * @var array
     */
    private $iframe_whitelist = array(
        'http://www.youtube.com',
        'https://www.youtube.com',
        'http://player.vimeo.com',
        'https://player.vimeo.com',
        'http://www.dailymotion.com',
        'https://www.dailymotion.com',
    );

    /**
     * Blacklisted resources
     *
     * @access private
     * @var array
     */
    private $media_blacklist = array(
        'api.flattr.com',
        'feeds.feedburner.com',
        'share.feedsportal.com',
        'da.feedsportal.com',
        'rss.feedsportal.com',
        'res.feedsportal.com',
        'res1.feedsportal.com',
        'res2.feedsportal.com',
        'res3.feedsportal.com',
        'pi.feedsportal.com',
        'rss.nytimes.com',
        'feeds.wordpress.com',
        'stats.wordpress.com',
        'rss.cnn.com',
        'twitter.com/home?status=',
        'twitter.com/share',
        'twitter_icon_large.png',
        'www.facebook.com/sharer.php',
        'facebook_icon_large.png',
        'plus.google.com/share',
        'www.gstatic.com/images/icons/gplus-16.png',
        'www.gstatic.com/images/icons/gplus-32.png',
        'www.gstatic.com/images/icons/gplus-64.png',
    );

    /**
     * Attributes used for external resources
     *
     * @access private
     * @var array
     */
    private $media_attributes = array(
        'src',
        'href',
        'poster',
    );

    /**
     * Attributes that must be integer
     *
     * @access private
     * @var array
     */
    private $integer_attributes = array(
        'width',
        'height',
        'frameborder',
    );

    /**
     * Mandatory attributes for specified tags
     *
     * @access private
     * @var array
     */
    private $required_attributes = array(
        'a' => array('href'),
        'img' => array('src'),
        'iframe' => array('src'),
        'audio' => array('src'),
        'source' => array('src'),
    );

    /**
     * Add attributes to specified tags
     *
     * @access private
     * @var array
     */
    private $add_attributes = array(
        'a' => array('rel' => 'noreferrer', 'target' => '_blank')
    );

    /**
     * List of filters to apply
     *
     * @access private
     * @var array
     */
    private $filters = array(
        'filterEmptyAttribute',
        'filterAllowedAttribute',
        'filterIntegerAttribute',
        'filterAbsoluteUrlAttribute',
        'filterIframeAttribute',
        'filterBlacklistResourceAttribute',
        'filterProtocolUrlAttribute',
    );

    /**
     * Add attributes to specified tags
     *
     * @access private
     * @var \PicoFeed\Url
     */
    private $website = null;

    /**
     * Constructor
     *
     * @access public
     * @param  \PicoFeed\Url    $website    Website url instance
     */
    public function __construct(Url $website)
    {
        $this->website = $website;
    }

    /**
     * Apply filters to the attributes list
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  array     $attributes    Attributes dictionary
     * @return array                    Filtered attributes
     */
    public function filter($tag, array $attributes)
    {
        foreach ($attributes as $attribute => &$value) {
            foreach ($this->filters as $filter) {
                if (! $this->$filter($tag, $attribute, $value)) {
                    unset($attributes[$attribute]);
                    break;
                }
            }
        }

        return $attributes;
    }

    /**
     * Return true if the value is not empty (remove empty attributes)
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterEmptyAttribute($tag, $attribute, $value)
    {
        return $value !== '';
    }

    /**
     * Return true if the value is allowed (remove not allowed attributes)
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterAllowedAttribute($tag, $attribute, $value)
    {
        return isset($this->attribute_whitelist[$tag]) && in_array($attribute, $this->attribute_whitelist[$tag]);
    }

    /**
     * Return true if the value is not integer (remove attributes that should have an integer value)
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterIntegerAttribute($tag, $attribute, $value)
    {
        if (in_array($attribute, $this->integer_attributes)) {
            return ctype_digit($value);
        }

        return true;
    }

    /**
     * Return true if the iframe source is allowed (remove not allowed iframe)
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterIframeAttribute($tag, $attribute, $value)
    {
        if ($tag === 'iframe' && $attribute === 'src') {

            foreach ($this->iframe_whitelist as $url) {
                if (strpos($value, $url) === 0) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Return true if the resource is not blacklisted (remove blacklisted resource attributes)
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterBlacklistResourceAttribute($tag, $attribute, $value)
    {
        if ($this->isResource($attribute) && $this->isBlacklistedMedia($value)) {
            return false;
        }

        return true;
    }

    /**
     * Convert all relative links to absolute url
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterAbsoluteUrlAttribute($tag, $attribute, &$value)
    {
        if ($this->isResource($attribute)) {
            $value = Url::resolve($value, $this->website);
        }

        return true;
    }

    /**
     * Return true if the scheme is authorized
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attribute     Atttribute name
     * @param  string    $value         Atttribute value
     * @return boolean
     */
    public function filterProtocolUrlAttribute($tag, $attribute, $value)
    {
        if ($this->isResource($attribute) && ! $this->isAllowedProtocol($value)) {
            return false;
        }

        return true;
    }

    /**
     * Automatically add/override some attributes for specific tags
     *
     * @access public
     * @param  string    $tag            Tag name
     * @param  array     $attributes     Atttributes list
     * @return array
     */
    public function addAttributes($tag, array $attributes)
    {
        if (isset($this->add_attributes[$tag])) {
            $attributes += $this->add_attributes[$tag];
        }

        return $attributes;
    }

    /**
     * Return true if all required attributes are present
     *
     * @access public
     * @param  string    $tag            Tag name
     * @param  array     $attributes     Atttributes list
     * @return boolean
     */
    public function hasRequiredAttributes($tag, array $attributes)
    {
        if (isset($this->required_attributes[$tag])) {

            foreach ($this->required_attributes[$tag] as $attribute) {
                if (! isset($attributes[$attribute])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if an attribute name is an external resource
     *
     * @access public
     * @param  string  $data  Attribute name
     * @return boolean
     */
    public function isResource($attribute)
    {
        return in_array($attribute, $this->media_attributes);
    }

    /**
     * Detect if the protocol is allowed or not
     *
     * @access public
     * @param  string  $value  Attribute value
     * @return boolean
     */
    public function isAllowedProtocol($value)
    {
        foreach ($this->scheme_whitelist as $protocol) {

            if (strpos($value, $protocol) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect if an url is blacklisted
     *
     * @access public
     * @param  string  $resouce  Attribute value (URL)
     * @return boolean
     */
    public function isBlacklistedMedia($resource)
    {
        foreach ($this->media_blacklist as $name) {

            if (strpos($resource, $name) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the attribute list to html
     *
     * @access public
     * @param  array     $attributes    Attributes
     * @return string
     */
    public function toHtml(array $attributes)
    {
        $html = array();

        foreach ($attributes as $attribute => $value) {
            $html[] = sprintf('%s="%s"', $attribute, Filter::escape($value));
        }

        return implode(' ', $html);
    }

    /**
     * Set whitelisted tags adn attributes for each tag
     *
     * @access public
     * @param  array   $values   List of tags: ['video' => ['src', 'cover'], 'img' => ['src']]
     * @return \PicoFeed\Filter
     */
    public function setWhitelistedAttributes(array $values)
    {
        $this->attribute_whitelist = $values ?: $this->attribute_whitelist;
        return $this;
    }

    /**
     * Set scheme whitelist
     *
     * @access public
     * @param  array   $values   List of scheme: ['http://', 'ftp://']
     * @return \PicoFeed\Filter
     */
    public function setSchemeWhitelist(array $values)
    {
        $this->scheme_whitelist = $values ?: $this->scheme_whitelist;
        return $this;
    }

    /**
     * Set media attributes (used to load external resources)
     *
     * @access public
     * @param  array   $values   List of values: ['src', 'href']
     * @return \PicoFeed\Filter
     */
    public function setMediaAttributes(array $values)
    {
        $this->media_attributes = $values ?: $this->media_attributes;
        return $this;
    }

    /**
     * Set blacklisted external resources
     *
     * @access public
     * @param  array   $values   List of tags: ['http://google.com/', '...']
     * @return \PicoFeed\Filter
     */
    public function setMediaBlacklist(array $values)
    {
        $this->media_blacklist = $values ?: $this->media_blacklist;
        return $this;
    }

    /**
     * Set mandatory attributes for whitelisted tags
     *
     * @access public
     * @param  array   $values   List of tags: ['img' => 'src']
     * @return \PicoFeed\Filter
     */
    public function setRequiredAttributes(array $values)
    {
        $this->required_attributes = $values ?: $this->required_attributes;
        return $this;
    }

    /**
     * Set attributes to automatically to specific tags
     *
     * @access public
     * @param  array   $values   List of tags: ['a' => 'target="_blank"']
     * @return \PicoFeed\Filter
     */
    public function setAttributeOverrides(array $values)
    {
        $this->add_attributes = $values ?: $this->add_attributes;
        return $this;
    }

    /**
     * Set attributes that must be an integer
     *
     * @access public
     * @param  array   $values   List of tags: ['width', 'height']
     * @return \PicoFeed\Filter
     */
    public function setIntegerAttributes(array $values)
    {
        $this->integer_attributes = $values ?: $this->integer_attributes;
        return $this;
    }

    /**
     * Set allowed iframe resources
     *
     * @access public
     * @param  array   $values   List of tags: ['http://www.youtube.com']
     * @return \PicoFeed\Filter
     */
    public function setIframeWhitelist(array $values)
    {
        $this->iframe_whitelist = $values ?: $this->iframe_whitelist;
        return $this;
    }
}
