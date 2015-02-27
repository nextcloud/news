<?php

namespace PicoFeed\Parser;

/**
 * Feed Item
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class Item
{
    /**
     * List of known RTL languages
     *
     * @access public
     * @var public
     */
    public $rtl = array(
        'ar',  // Arabic (ar-**)
        'fa',  // Farsi (fa-**)
        'ur',  // Urdu (ur-**)
        'ps',  // Pashtu (ps-**)
        'syr', // Syriac (syr-**)
        'dv',  // Divehi (dv-**)
        'he',  // Hebrew (he-**)
        'yi',  // Yiddish (yi-**)
    );

    /**
     * Item id
     *
     * @access public
     * @var string
     */
    public $id = '';

    /**
     * Item title
     *
     * @access public
     * @var string
     */
    public $title = '';

    /**
     * Item url
     *
     * @access public
     * @var string
     */
    public $url = '';

    /**
     * Item author
     *
     * @access public
     * @var string
     */
    public $author= '';

    /**
     * Item date
     *
     * @access public
     * @var \DateTime
     */
    public $date = null;

    /**
     * Item content
     *
     * @access public
     * @var string
     */
    public $content = '';

    /**
     * Item enclosure url
     *
     * @access public
     * @var string
     */
    public $enclosure_url = '';

    /**
     * Item enclusure type
     *
     * @access public
     * @var string
     */
    public $enclosure_type = '';

    /**
     * Item language
     *
     * @access public
     * @var string
     */
    public $language = '';

    /**
     * Return item information
     *
     * @access public
     * $return string
     */
    public function __toString()
    {
        $output = '';

        foreach (array('id', 'title', 'url', 'language', 'author', 'enclosure_url', 'enclosure_type') as $property) {
            $output .= 'Item::'.$property.' = '.$this->$property.PHP_EOL;
        }

        $output .= 'Item::date = '.$this->date->format(DATE_RFC822).PHP_EOL;
        $output .= 'Item::isRTL() = '.($this->isRTL() ? 'true' : 'false').PHP_EOL;
        $output .= 'Item::content = '.strlen($this->content).' bytes'.PHP_EOL;

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
     * Get content
     *
     * @access public
     * $return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get enclosure url
     *
     * @access public
     * $return string
     */
    public function getEnclosureUrl()
    {
        return $this->enclosure_url;
    }

    /**
     * Get enclosure type
     *
     * @access public
     * $return string
     */
    public function getEnclosureType()
    {
        return $this->enclosure_type;
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
     * Get author
     *
     * @access public
     * $return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Return true if the item is "Right to Left"
     *
     * @access public
     * @return bool
     */
    public function isRTL()
    {
        return Parser::isLanguageRTL($this->language);
    }
}
