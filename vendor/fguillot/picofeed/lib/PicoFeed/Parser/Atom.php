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
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedUrl(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->feed_url = $this->getUrl($xml, 'self');
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
        $feed->site_url = $this->getUrl($xml, 'alternate', true);
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
        $feed->description = (string) $xml->subtitle;
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
        $feed->logo = (string) $xml->logo;
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
        $feed->title = Filter::stripWhiteSpace((string) $xml->title) ?: $feed->getSiteUrl();
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
        $feed->language = XmlParser::getXmlLang($this->content);
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
        $feed->id = (string) $xml->id;
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
        $feed->date = $this->parseDate((string) $xml->updated);
    }

    /**
     * Find the item date
     *
     * @access public
     * @param  SimpleXMLElement   $entry   Feed item
     * @param  Item               $item    Item object
     */
    public function findItemDate(SimpleXMLElement $entry, Item $item)
    {
        $published = isset($entry->published) ? $this->parseDate((string) $entry->published) : 0;
        $updated = isset($entry->updated) ? $this->parseDate((string) $entry->updated) : 0;

        $item->date = max($published, $updated) ?: time();
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
     * @param  SimpleXMLElement          $xml     Feed
     * @param  SimpleXMLElement          $entry   Feed item
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
        $item->url = $this->getUrl($entry, 'alternate');
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
        $enclosure = $this->findLink($entry, 'enclosure');

        if ($enclosure) {
            $item->enclosure_url = Url::resolve((string) $enclosure['href'], $feed->getSiteUrl());
            $item->enclosure_type = (string) $enclosure['type'];
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
     * @access private
     * @param  SimpleXMLElement   $xml      XML tag
     * @param  string             $rel      Link relationship: alternate, enclosure, related, self, via
     * @return string
     */
    private function getUrl(SimpleXMLElement $xml, $rel, $fallback = false)
    {
        $link = $this->findLink($xml, $rel);

        if ($link) {
            return (string) $link['href'];
        }

        if ($fallback) {
            $link = $this->findLink($xml, '');
            return $link ? (string) $link['href'] : '';
        }

        return '';
    }

    /**
     * Get a link tag that match a relationship
     *
     * @access private
     * @param  SimpleXMLElement   $xml      XML tag
     * @param  string             $rel      Link relationship: alternate, enclosure, related, self, via
     * @return SimpleXMLElement|null
     */
    private function findLink(SimpleXMLElement $xml, $rel)
    {
        foreach ($xml->link as $link) {
            if (empty($rel) || $rel === (string) $link['rel']) {
                return $link;
            }
        }

        return null;
    }

    /**
     * Get the entry content
     *
     * @access private
     * @param  SimpleXMLElement   $entry   XML Entry
     * @return string
     */
    private function getContent(SimpleXMLElement $entry)
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
