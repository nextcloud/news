<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;
use PicoFeed\Filter\Filter;
use PicoFeed\Client\Url;

/**
 * RSS 2.0 Parser
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class Rss20 extends Parser
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
        return $xml->channel->item;
    }

    /**
     * Find the feed url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedUrl(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->feed_url = '';
    }

    /**
     * Find the site url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findSiteUrl(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->site_url = (string) $xml->channel->link;
    }

    /**
     * Find the feed description
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedDescription(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->description = (string) $xml->channel->description;
    }

    /**
     * Find the feed logo url
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedLogo(SimpleXMLElement $xml, Feed $feed)
    {
        if (isset($xml->channel->image->url)) {
            $feed->logo = (string) $xml->channel->image->url;
        }
    }

    /**
     * Find the feed title
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedTitle(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->title = Filter::stripWhiteSpace((string) $xml->channel->title) ?: $feed->getSiteUrl();
    }

    /**
     * Find the feed language
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedLanguage(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->language = isset($xml->channel->language) ? (string) $xml->channel->language : '';
    }

    /**
     * Find the feed id
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedId(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->id = $feed->getFeedUrl() ?: $feed->getSiteUrl();
    }

    /**
     * Find the feed date
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedDate(SimpleXMLElement $xml, Feed $feed)
    {
        $date = isset($xml->channel->pubDate) ? $xml->channel->pubDate : $xml->channel->lastBuildDate;
        $feed->date = $this->parseDate((string) $date);
    }

    /**
     * Find the item date
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemDate(SimpleXMLElement $entry, Item $item)
    {
        $date = XmlParser::getNamespaceValue($entry, $this->namespaces, 'date');

        if (empty($date)) {
            $date = XmlParser::getNamespaceValue($entry, $this->namespaces, 'updated');
        }

        if (empty($date)) {
            $date = (string) $entry->pubDate;
        }

        $item->date = $this->parseDate($date);
    }

    /**
     * Find the item title
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
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
     * @param  SimpleXMLElement          $xml     Feed
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemAuthor(SimpleXMLElement $xml, SimpleXMLElement $entry, Item $item)
    {
        $item->author = XmlParser::getNamespaceValue($entry, $this->namespaces, 'creator');

        if (empty($item->author)) {
            if (isset($entry->author)) {
                $item->author = (string) $entry->author;
            }
            else if (isset($xml->channel->webMaster)) {
                $item->author = (string) $xml->channel->webMaster;
            }
        }
    }

    /**
     * Find the item content
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemContent(SimpleXMLElement $entry, Item $item)
    {
        $content = XmlParser::getNamespaceValue($entry, $this->namespaces, 'encoded');

        if (empty($content) && $entry->description->count() > 0) {
            $content = (string) $entry->description;
        }

        $item->content = $content;
    }

    /**
     * Find the item URL
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     */
    public function findItemUrl(SimpleXMLElement $entry, Item $item)
    {
        $links = array(
            XmlParser::getNamespaceValue($entry, $this->namespaces, 'origLink'),
            isset($entry->link) ? (string) $entry->link : '',
            XmlParser::getNamespaceValue($entry, $this->namespaces, 'link', 'href'),
            isset($entry->guid) ? (string) $entry->guid : '',
        );

        foreach ($links as $link) {
            if (! empty($link) && filter_var($link, FILTER_VALIDATE_URL) !== false) {
                $item->url = $link;
                break;
            }
        }
    }

    /**
     * Genereate the item id
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemId(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $id = (string) $entry->guid;

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
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  \PicoFeed\Parser\Item     $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemEnclosure(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        if (isset($entry->enclosure)) {

            $item->enclosure_url = XmlParser::getNamespaceValue($entry->enclosure, $this->namespaces, 'origEnclosureLink');

            if (empty($item->enclosure_url)) {
                $item->enclosure_url = isset($entry->enclosure['url']) ? (string) $entry->enclosure['url'] : '';
            }

            $item->enclosure_type = isset($entry->enclosure['type']) ? (string) $entry->enclosure['type'] : '';
            $item->enclosure_url = Url::resolve($item->enclosure_url, $feed->getSiteUrl());
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
}
