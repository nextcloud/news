<?php

namespace PicoFeed\Parsers;

require_once __DIR__.'/Rss20.php';

use SimpleXMLElement;
use PicoFeed\Feed;
use PicoFeed\Item;
use PicoFeed\XmlParser;
use PicoFeed\Parsers\Rss20;

/**
 * RSS 1.0 parser
 *
 * @author  Frederic Guillot
 * @package parser
 */
class Rss10 extends Rss20
{
    /**
     * Get the path to the items XML tree
     *
     * @access public
     * @param  SimpleXMLElement   $xml   Feed xml
     * @return SimpleXMLElement
     */
    public function getItemsTree(SimpleXMLElement $xml)
    {
        return $xml->item;
    }

    /**
     * Find the feed date
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Feed     $feed    Feed object
     */
    public function findFeedDate(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->date = $this->parseDate(XmlParser::getNamespaceValue($xml->channel, $this->namespaces, 'date'));
    }

    /**
     * Find the feed language
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Feed     $feed    Feed object
     */
    public function findFeedLanguage(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->language = XmlParser::getNamespaceValue($xml->channel, $this->namespaces, 'language');
    }

    /**
     * Genereate the item id
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Item     $item    Item object
     * @param  \PicoFeed\Feed     $feed    Feed object
     */
    public function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        if ($this->isExcludedFromId($feed->url)) {
            $feed_permalink = '';
        }
        else {
            $feed_permalink = $feed->url;
        }

        $item->id = $this->generateId($item->url,  $feed_permalink);
    }

    /**
     * Find the item enclosure
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Item     $item    Item object
     * @param  \PicoFeed\Feed     $feed    Feed object
     */
    public function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
    }
}
