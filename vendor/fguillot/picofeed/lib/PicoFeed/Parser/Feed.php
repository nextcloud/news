<?php

namespace PicoFeed\Parser;

/**
 * Feed
 *
 * @author  Frederic Guillot
 * @package Parser
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
    public $feed_url = '';

    /**
     * Site url
     *
     * @access public
     * @var string
     */
    public $site_url = '';

    /**
     * Feed date
     *
     * @access public
     * @var \DateTime
     */
    public $date = null;

    /**
     * Feed language
     *
     * @access public
     * @var string
     */
    public $language = '';

    /**
     * Feed logo URL
     *
     * @access public
     * @var string
     */
    public $logo = '';

    /**
     * Feed icon URL
     *
     * @access public
     * @var string
     */
    public $icon = '';

    /**
     * Return feed information
     *
     * @access public
     * $return string
     */
    public function __toString()
    {
        $output = '';

        foreach (array('id', 'title', 'feed_url', 'site_url', 'language', 'description', 'logo') as $property) {
            $output .= 'Feed::'.$property.' = '.$this->$property.PHP_EOL;
        }

        $output .= 'Feed::date = '.$this->date->format(DATE_RFC822).PHP_EOL;
        $output .= 'Feed::isRTL() = '.($this->isRTL() ? 'true' : 'false').PHP_EOL;
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
     * Get the icon url
     *
     * @access public
     * $return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Get feed url
     *
     * @access public
     * $return string
     */
    public function getFeedUrl()
    {
        return $this->feed_url;
    }

    /**
     * Get site url
     *
     * @access public
     * $return string
     */
    public function getSiteUrl()
    {
        return $this->site_url;
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

    /**
     * Return true if the feed is "Right to Left"
     *
     * @access public
     * @return bool
     */
    public function isRTL()
    {
        return Parser::isLanguageRTL($this->language);
    }
}
