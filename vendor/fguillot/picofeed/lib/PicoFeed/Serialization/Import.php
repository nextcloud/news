<?php

namespace PicoFeed\Serialization;

use SimpleXmlElement;
use StdClass;
use PicoFeed\Logging\Logger;
use PicoFeed\Parser\XmlParser;

/**
 * OPML Import.
 *
 * @author  Frederic Guillot
 */
class Import
{
    /**
     * OPML file content.
     *
     * @var string
     */
    private $content = '';

    /**
     * Subscriptions.
     *
     * @var array
     */
    private $items = array();

    /**
     * Constructor.
     *
     * @param string $content OPML file content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Parse the OPML file.
     *
     * @return array|false
     */
    public function execute()
    {
        Logger::setMessage(get_called_class().': start importation');

        $xml = XmlParser::getSimpleXml(trim($this->content));

        if ($xml === false || $xml->getName() !== 'opml' || !isset($xml->body)) {
            Logger::setMessage(get_called_class().': OPML tag not found or malformed XML document');

            return false;
        }

        $this->parseEntries($xml->body);
        Logger::setMessage(get_called_class().': '.count($this->items).' subscriptions found');

        return $this->items;
    }

    /**
     * Parse each entries of the subscription list.
     *
     * @param SimpleXMLElement $tree XML node
     */
    public function parseEntries(SimpleXMLElement $tree)
    {
        if (isset($tree->outline)) {
            foreach ($tree->outline as $item) {
                if (isset($item->outline)) {
                    $this->parseEntries($item);
                } elseif ((isset($item['text']) || isset($item['title'])) && isset($item['xmlUrl'])) {
                    $entry = new StdClass();
                    $entry->category = $this->findCategory($tree);
                    $entry->title = $this->findTitle($item);
                    $entry->feed_url = $this->findFeedUrl($item);
                    $entry->site_url = $this->findSiteUrl($item, $entry);
                    $entry->type = $this->findType($item);
                    $entry->description = $this->findDescription($item, $entry);
                    $this->items[] = $entry;
                }
            }
        }
    }

    /**
     * Find category.
     *
     * @param SimpleXmlElement $tree XML tree
     *
     * @return string
     */
    public function findCategory(SimpleXmlElement $tree)
    {
        return isset($tree['title']) ? (string) $tree['title'] : (string) $tree['text'];
    }

    /**
     * Find title.
     *
     * @param SimpleXmlElement $item XML tree
     *
     * @return string
     */
    public function findTitle(SimpleXmlElement $item)
    {
        return isset($item['title']) ? (string) $item['title'] : (string) $item['text'];
    }

    /**
     * Find feed url.
     *
     * @param SimpleXmlElement $item XML tree
     *
     * @return string
     */
    public function findFeedUrl(SimpleXmlElement $item)
    {
        return (string) $item['xmlUrl'];
    }

    /**
     * Find site url.
     *
     * @param SimpleXmlElement $item  XML tree
     * @param StdClass         $entry Feed entry
     *
     * @return string
     */
    public function findSiteUrl(SimpleXmlElement $item, StdClass $entry)
    {
        return isset($item['htmlUrl']) ? (string) $item['htmlUrl'] : $entry->feed_url;
    }

    /**
     * Find type.
     *
     * @param SimpleXmlElement $item XML tree
     *
     * @return string
     */
    public function findType(SimpleXmlElement $item)
    {
        return isset($item['version']) ? (string) $item['version'] : isset($item['type']) ? (string) $item['type'] : 'rss';
    }

    /**
     * Find description.
     *
     * @param SimpleXmlElement $item  XML tree
     * @param StdClass         $entry Feed entry
     *
     * @return string
     */
    public function findDescription(SimpleXmlElement $item, StdClass $entry)
    {
        return isset($item['description']) ? (string) $item['description'] : $entry->title;
    }
}
