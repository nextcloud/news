<?php

namespace PicoFeed\Filter;

/**
 * Tag Filter class
 *
 * @author  Frederic Guillot
 * @package Filter
 */
class Tag
{
    /**
     * Tags whitelist
     *
     * @access private
     * @var array
     */
    private $tag_whitelist = array(
        'audio',
        'video',
        'source',
        'dt',
        'dd',
        'dl',
        'table',
        'caption',
        'tr',
        'th',
        'td',
        'tbody',
        'thead',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'strong',
        'em',
        'code',
        'pre',
        'blockquote',
        'p',
        'ul',
        'li',
        'ol',
        'br',
        'del',
        'a',
        'img',
        'figure',
        'figcaption',
        'cite',
        'time',
        'abbr',
        'iframe',
        'q',
    );

    /**
     * Check if the tag is allowed and is not a pixel tracker
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  array     $attributes    Attributes dictionary
     * @return boolean
     */
    public function isAllowed($tag, array $attributes)
    {
        return $this->isAllowedTag($tag) && ! $this->isPixelTracker($tag, $attributes);
    }

    /**
     * Return the HTML opening tag
     *
     * @access public
     * @param  string    $tag           Tag name
     * @param  string    $attributes    Attributes converted in html
     * @return string
     */
    public function openHtmlTag($tag, $attributes = '')
    {
        return '<'.$tag.(empty($attributes) ? '' : ' '.$attributes).($this->isSelfClosingTag($tag) ? '/>' : '>');
    }

    /**
     * Return the HTML closing tag
     *
     * @access public
     * @param  string    $tag           Tag name
     * @return string
     */
    public function closeHtmlTag($tag)
    {
        return $this->isSelfClosingTag($tag) ? '' : '</'.$tag.'>';
    }

    /**
     * Return true is the tag is self-closing
     *
     * @access public
     * @param  string    $tag           Tag name
     * @return boolean
     */
    public function isSelfClosingTag($tag)
    {
        return in_array($tag, array('br', 'img'));
    }

    /**
     * Check if a tag is on the whitelist
     *
     * @access public
     * @param  string     $tag    Tag name
     * @return boolean
     */
    public function isAllowedTag($tag)
    {
        return in_array($tag, $this->tag_whitelist);
    }

    /**
     * Detect if an image tag is a pixel tracker
     *
     * @access public
     * @param  string  $tag         Tag name
     * @param  array   $attributes  Tag attributes
     * @return boolean
     */
    public function isPixelTracker($tag, array $attributes)
    {
        return $tag === 'img' &&
                isset($attributes['height']) && isset($attributes['width']) &&
                $attributes['height'] == 1 && $attributes['width'] == 1;
    }

    /**
     * Remove empty tags
     *
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public function removeEmptyTags($data)
    {
        return preg_replace('/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU', '', $data);
    }

    /**
     * Replace <br/><br/> by only one
     *
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public function removeMultipleTags($data)
    {
        return preg_replace("/(<br\s*\/?>\s*)+/", "<br/>", $data);
    }

    /**
     * Set whitelisted tags adn attributes for each tag
     *
     * @access public
     * @param  array   $values   List of tags: ['video' => ['src', 'cover'], 'img' => ['src']]
     * @return \PicoFeed\Filter
     */
    public function setWhitelistedTags(array $values)
    {
        $this->tag_whitelist = $values ?: $this->tag_whitelist;
        return $this;
    }
}
