<?php

namespace PicoFeed;

/**
 * Feed
 *
 * @author  Frederic Guillot
 * @package picofeed
 */
class Feed
{
    /**
     * Feed items
     *
     * @access public
     * @var array
     */
    public $items = array();

    /**
     * Feed id
     *
     * @access public
     * @var string
     */
    public $id = '';

    /**
     * Feed title
     *
     * @access public
     * @var string
     */
    public $title = '';

    /**
     * Feed description
     *
     * @access public
     * @var string
     */
    public $description = '';

    /**
     * Feed url
     *
     * @access public
     * @var string
     */
    public $url = '';

    /**
     * Feed date
     *
     * @access public
     * @var integer
     */
    public $date = 0;

    /**
     * Feed language
     *
     * @access public
     * @var string
     */
    public $language = '';

    /**
     * Feed logo URL (not the same as icon)
     *
     * @access public
     * @var string
     */
    public $logo = '';

    /**
     * Return feed information
     *
     * @access public
     * $return string
     */
    public function __toString()
    {
        $output = '';

        foreach (array('id', 'title', 'url', 'date', 'language', 'description', 'logo') as $property) {
            $output .= 'Feed::'.$property.' = '.$this->$property.PHP_EOL;
        }

        $output .= 'Feed::items = '.count($this->items).' items'.PHP_EOL;

        foreach ($this->items as $item) {
            $output .= '----'.PHP_EOL;
            $output .= $item;
        }

        return $output;
    }

    /**
     * Get title
     *
     * @access public
     * $return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     *
     * @access public
     * $return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the logo url
     *
     * @access public
     * $return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get url
     *
     * @access public
     * $return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get date
     *
     * @access public
     * $return integer
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get language
     *
     * @access public
     * $return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get id
     *
     * @access public
     * $return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get feed items
     *
     * @access public
     * $return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
