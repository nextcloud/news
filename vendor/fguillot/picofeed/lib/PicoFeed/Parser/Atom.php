<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;
use PicoFeed\Filter\Filter;
use PicoFeed\Client\Url;

/**
 * Atom parser
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class Atom extends Parser
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
        return $xml->entry;
    }

    /**
     * Find the feed url
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedUrl(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->url = $this->getLink($xml);
    }

    /**
     * Find the feed description
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedDescription(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->description = (string) $xml->subtitle;
    }

    /**
     * Find the feed logo url
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedLogo(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->logo = (string) $xml->logo;
    }

    /**
     * Find the feed title
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedTitle(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->title = Filter::stripWhiteSpace((string) $xml->title) ?: $feed->url;
    }

    /**
     * Find the feed language
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedLanguage(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->language = XmlParser::getXmlLang($this->content);
    }

    /**
     * Find the feed id
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedId(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->id = (string) $xml->id;
    }

    /**
     * Find the feed date
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedDate(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->date = $this->parseDate((string) $xml->updated);
    }

    /**
     * Find the item date
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  Item           $item    Item object
     */
    public function findItemDate(SimpleXMLElement $entry, Item $item)
    {
        $item->date = $this->parseDate((string) $entry->updated);
    }

    /**
     * Find the item title
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  Item               $item    Item object
     */
    public function findItemTitle(SimpleXMLElement $entry, Item $item)
    {
        $item->title = Filter::stripWhiteSpace((string) $entry->title);

        if (empty($item->title)) {
            $item->title = $item->url;
        }
    }

    /**
     * Find the item author
     *
     * @access public
     * @param  SimpleXMLElement   $xml     Feed
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemAuthor(SimpleXMLElement $xml, SimpleXMLElement $entry, Item $item)
    {
        if (isset($entry->author->name)) {
            $item->author = (string) $entry->author->name;
        }
        else {
            $item->author = (string) $xml->author->name;
        }
    }

    /**
     * Find the item content
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemContent(SimpleXMLElement $entry, Item $item)
    {
        $item->content = $this->getContent($entry);
    }

    /**
     * Find the item URL
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemUrl(SimpleXMLElement $entry, Item $item)
    {
        $item->url = $this->getLink($entry);
    }

    /**
     * Genereate the item id
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $id = (string) $entry->id;

        if ($id) {
            $item->id = $this->generateId($id);
        }
        else {
            $item->id = $this->generateId(
                $item->getTitle(), $item->getUrl(), $item->getContent()
            );
        }
    }

    /**
     * Find the item enclosure
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        foreach ($entry->link as $link) {
            if ((string) $link['rel'] === 'enclosure') {

                $item->enclosure_url = Url::resolve((string) $link['href'], $feed->url);
                $item->enclosure_type = (string) $link['type'];
                break;
            }
        }
    }

    /**
     * Find the item language
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemLanguage(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $item->language = $feed->language;
    }

    /**
     * Get the URL from a link tag
     *
     * @access public
     * @param  SimpleXMLElement   $xml    XML tag
     * @return string
     */
    public function getLink(SimpleXMLElement $xml)
    {
        foreach ($xml->link as $link) {
            if ((string) $link['type'] === 'text/html' || (string) $link['type'] === 'application/xhtml+xml') {
                return (string) $link['href'];
            }
        }

        return (string) $xml->link['href'];
    }

    /**
     * Get the entry content
     *
     * @access public
     * @param  SimpleXMLElement   $entry   XML Entry
     * @return string
     */
    public function getContent(SimpleXMLElement $entry)
    {
        if (isset($entry->content) && ! empty($entry->content)) {

            if (count($entry->content->children())) {
                return (string) $entry->content->asXML();
            }
            else {
                return (string) $entry->content;
            }
        }
        else if (isset($entry->summary) && ! empty($entry->summary)) {
            return (string) $entry->summary;
        }

        return '';
    }
}
