<?php

namespace PicoFeed\Parser;

use SimpleXMLElement;
use PicoFeed\Filter\Filter;

/**
 * RSS 1.0 parser
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class Rss10 extends Parser
{
    /**
     * Supported namespaces
     */
    protected $namespaces = array(
        'rss' => 'http://purl.org/rss/1.0/',
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'feedburner' => 'http://rssnamespace.org/feedburner/ext/1.0',
    );

    /**
     * Get the path to the items XML tree
     *
     * @access public
     * @param  SimpleXMLElement   $xml   Feed xml
     * @return SimpleXMLElement
     */
    public function getItemsTree(SimpleXMLElement $xml)
    {
        return XmlParser::getXPathResult($xml, 'rss:item', $this->namespaces)
               ?: XmlParser::getXPathResult($xml, 'item');
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
        $site_url = XmlParser::getXPathResult($xml, 'rss:channel/rss:link', $this->namespaces)
                    ?: XmlParser::getXPathResult($xml, 'channel/link');

        $feed->site_url = (string) current($site_url);
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
        $description = XmlParser::getXPathResult($xml, 'rss:channel/rss:description', $this->namespaces)
                       ?: XmlParser::getXPathResult($xml, 'channel/description');

        $feed->description = (string) current($description);
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
        $logo = XmlParser::getXPathResult($xml, 'rss:image/rss:url', $this->namespaces)
                ?: XmlParser::getXPathResult($xml, 'image/url');

        $feed->logo = (string) current($logo);
    }

    /**
     * Find the feed icon
     *
     * @access public
     * @param  SimpleXMLElement          $xml     Feed xml
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findFeedIcon(SimpleXMLElement $xml, Feed $feed)
    {
        $feed->icon = '';
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
        $title = XmlParser::getXPathResult($xml, 'rss:channel/rss:title', $this->namespaces)
                 ?: XmlParser::getXPathResult($xml, 'channel/title');

        $feed->title = Filter::stripWhiteSpace((string) current($title)) ?: $feed->getSiteUrl();
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
        $language = XmlParser::getXPathResult($xml, 'rss:channel/dc:language', $this->namespaces)
                    ?: XmlParser::getXPathResult($xml, 'channel/dc:language', $this->namespaces);

        $feed->language = (string) current($language);
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
        $date = XmlParser::getXPathResult($xml, 'rss:channel/dc:date', $this->namespaces)
                ?: XmlParser::getXPathResult($xml, 'channel/dc:date', $this->namespaces);

        $feed->date = $this->date->getDateTime((string) current($date));
    }

    /**
     * Find the item date
     *
     * @access public
     * @param  SimpleXMLElement          $entry   Feed item
     * @param  Item                      $item    Item object
     * @param  \PicoFeed\Parser\Feed     $feed    Feed object
     */
    public function findItemDate(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $date = XmlParser::getXPathResult($entry, 'dc:date', $this->namespaces);

        $item->date = empty($date) ? $feed->getDate() : $this->date->getDateTime((string) current($date));
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
        $title = XmlParser::getXPathResult($entry, 'rss:title', $this->namespaces)
                 ?: XmlParser::getXPathResult($entry, 'title');

        $item->title = Filter::stripWhiteSpace((string) current($title)) ?: $item->url;
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
        $author = XmlParser::getXPathResult($entry, 'dc:creator', $this->namespaces)
                  ?: XmlParser::getXPathResult($xml, 'rss:channel/dc:creator', $this->namespaces)
                  ?: XmlParser::getXPathResult($xml, 'channel/dc:creator', $this->namespaces);

        $item->author = (string) current($author);
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
        $content = XmlParser::getXPathResult($entry, 'content:encoded', $this->namespaces);

        if (trim((string) current($content)) === '') {
            $content = XmlParser::getXPathResult($entry, 'rss:description', $this->namespaces)
                       ?: XmlParser::getXPathResult($entry, 'description');
        }

        $item->content = (string) current($content);
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
        $link = XmlParser::getXPathResult($entry, 'feedburner:origLink', $this->namespaces)
                ?: XmlParser::getXPathResult($entry, 'rss:link', $this->namespaces)
                ?: XmlParser::getXPathResult($entry, 'link');

        $item->url = trim((string) current($link));
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
        $item->id = $this->generateId(
            $item->getTitle(), $item->getUrl(), $item->getContent()
        );
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
        $language = XmlParser::getXPathResult($entry, 'dc:language', $this->namespaces);

        $item->language = (string) current($language) ?: $feed->language;
    }
}
