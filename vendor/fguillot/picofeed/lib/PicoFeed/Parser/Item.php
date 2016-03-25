<?php

namespace PicoFeed\Parser;

/**
 * Feed Item.
 *
 * @author  Frederic Guillot
 */
class Item
{
    /**
     * List of known RTL languages.
     *
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
     * Item id.
     *
     * @var string
     */
    public $id = '';

    /**
     * Item title.
     *
     * @var string
     */
    public $title = '';

    /**
     * Item url.
     *
     * @var string
     */
    public $url = '';

    /**
     * Item author.
     *
     * @var string
     */
    public $author = '';

    /**
     * Item date.
     *
     * @var \DateTime
     */
    public $date = null;

    /**
     * Item content.
     *
     * @var string
     */
    public $content = '';

    /**
     * Item enclosure url.
     *
     * @var string
     */
    public $enclosure_url = '';

    /**
     * Item enclusure type.
     *
     * @var string
     */
    public $enclosure_type = '';

    /**
     * Item language.
     *
     * @var string
     */
    public $language = '';

    /**
     * Raw XML.
     *
     * @var \SimpleXMLElement
     */
    public $xml;

    /**
     * List of namespaces.
     *
     * @var array
     */
    public $namespaces = array();

    /**
     * Check if a XML namespace exists
     *
     * @access public
     * @param  string $namespace
     * @return bool
     */
    public function hasNamespace($namespace)
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Get specific XML tag or attribute value.
     *
     * @param string $tag       Tag name (examples: guid, media:content)
     * @param string $attribute Tag attribute
     *
     * @return array|false Tag values or error
     */
    public function getTag($tag, $attribute = '')
    {
        if ($attribute !== '') {
            $attribute = '/@'.$attribute;
        }

        $query = './/'.$tag.$attribute;
        $elements = XmlParser::getXPathResult($this->xml, $query, $this->namespaces);

        if ($elements === false) { // xPath error
            return false;
        }

        return array_map(function ($element) { return (string) $element;}, $elements);
    }

    /**
     * Return item information.
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
     * Get title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get URL
     *
     * @access public
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set URL
     *
     * @access public
     * @param  string $url
     * @return Item
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get date.
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @access public
     * @param  string $value
     * @return Item
     */
    public function setContent($value)
    {
        $this->content = $value;
        return $this;
    }

    /**
     * Get enclosure url.
     */
    public function getEnclosureUrl()
    {
        return $this->enclosure_url;
    }

    /**
     * Get enclosure type.
     */
    public function getEnclosureType()
    {
        return $this->enclosure_type;
    }

    /**
     * Get language.
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get author.
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Return true if the item is "Right to Left".
     *
     * @return bool
     */
    public function isRTL()
    {
        return Parser::isLanguageRTL($this->language);
    }
}
