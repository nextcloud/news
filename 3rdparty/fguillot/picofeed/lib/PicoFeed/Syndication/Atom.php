<?php

namespace PicoFeed\Syndication;

use DomDocument;
use DomElement;
use DomAttr;

/**
 * Atom writer class
 *
 * @author  Frederic Guillot
 * @package Syndication
 */
class Atom extends Writer
{
    /**
     * List of required properties for each feed
     *
     * @access private
     * @var array
     */
    private $required_feed_properties = array(
        'title',
        'site_url',
        'feed_url',
    );

    /**
     * List of required properties for each item
     *
     * @access private
     * @var array
     */
    private $required_item_properties = array(
        'title',
        'url',
    );

    /**
     * Get the Atom document
     *
     * @access public
     * @param  string   $filename   Optional filename
     * @return string
     */
    public function execute($filename = '')
    {
        $this->checkRequiredProperties($this->required_feed_properties, $this);

        $this->dom = new DomDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        // <feed/>
        $feed = $this->dom->createElement('feed');
        $feed->setAttributeNodeNS(new DomAttr('xmlns', 'http://www.w3.org/2005/Atom'));

        // <generator/>
        $generator = $this->dom->createElement('generator', 'PicoFeed');
        $generator->setAttribute('uri', 'https://github.com/fguillot/picoFeed');
        $feed->appendChild($generator);

        // <title/>
        $title = $this->dom->createElement('title');
        $title->appendChild($this->dom->createTextNode($this->title));
        $feed->appendChild($title);

        // <id/>
        $id = $this->dom->createElement('id');
        $id->appendChild($this->dom->createTextNode($this->site_url));
        $feed->appendChild($id);

        // <updated/>
        $this->addUpdated($feed, $this->updated);

        // <link rel="alternate" type="text/html" href="http://example.org/"/>
        $this->addLink($feed, $this->site_url);

        // <link rel="self" type="application/atom+xml" href="http://example.org/feed.atom"/>
        $this->addLink($feed, $this->feed_url, 'self', 'application/atom+xml');

        // <author/>
        if (isset($this->author)) $this->addAuthor($feed, $this->author);

        // <entry/>
        foreach ($this->items as $item) {
            $this->checkRequiredProperties($this->required_item_properties, $item);
            $feed->appendChild($this->createEntry($item));
        }

        $this->dom->appendChild($feed);

        if ($filename) {
            $this->dom->save($filename);
        }
        else {
            return $this->dom->saveXML();
        }
    }

    /**
     * Create item entry
     *
     * @access public
     * @param  arrray    $item    Item properties
     * @return DomElement
     */
    public function createEntry(array $item)
    {
        $entry = $this->dom->createElement('entry');

        // <title/>
        $title = $this->dom->createElement('title');
        $title->appendChild($this->dom->createTextNode($item['title']));
        $entry->appendChild($title);

        // <id/>
        $id = $this->dom->createElement('id');
        $id->appendChild($this->dom->createTextNode(isset($item['id']) ? $item['id'] : $item['url']));
        $entry->appendChild($id);

        // <updated/>
        $this->addUpdated($entry, isset($item['updated']) ? $item['updated'] : '');

        // <published/>
        if (isset($item['published'])) {
            $entry->appendChild($this->dom->createElement('published', date(DATE_ATOM, $item['published'])));
        }

        // <link rel="alternate" type="text/html" href="http://example.org/"/>
        $this->addLink($entry, $item['url']);

        // <summary/>
        if (isset($item['summary'])) {
            $summary = $this->dom->createElement('summary');
            $summary->appendChild($this->dom->createTextNode($item['summary']));
            $entry->appendChild($summary);
        }

        // <content/>
        if (isset($item['content'])) {
            $content = $this->dom->createElement('content');
            $content->setAttribute('type', 'html');
            $content->appendChild($this->dom->createCDATASection($item['content']));
            $entry->appendChild($content);
        }

        // <author/>
        if (isset($item['author'])) {
            $this->addAuthor($entry, $item['author']);
        }

        return $entry;
    }

    /**
     * Add Link
     *
     * @access public
     * @param  DomElement   $xml     XML node
     * @param  string       $url     URL
     * @param  string       $rel     Link rel attribute
     * @param  string       $type    Link type attribute
     */
    public function addLink(DomElement $xml, $url, $rel = 'alternate', $type = 'text/html')
    {
        $link = $this->dom->createElement('link');
        $link->setAttribute('rel', $rel);
        $link->setAttribute('type', $type);
        $link->setAttribute('href', $url);
        $xml->appendChild($link);
    }

    /**
     * Add publication date
     *
     * @access public
     * @param  DomElement   $xml     XML node
     * @param  integer      $value   Timestamp
     */
    public function addUpdated(DomElement $xml, $value = 0)
    {
        $xml->appendChild($this->dom->createElement(
            'updated',
            date(DATE_ATOM, $value ?: time())
        ));
    }

    /**
     * Add author
     *
     * @access public
     * @param  DomElement   $xml     XML node
     * @param  array        $values  Author name and email
     */
    public function addAuthor(DomElement $xml, array $values)
    {
        $author = $this->dom->createElement('author');

        if (isset($values['name'])) {
            $name = $this->dom->createElement('name');
            $name->appendChild($this->dom->createTextNode($values['name']));
            $author->appendChild($name);
        }

        if (isset($values['email'])) {
            $email = $this->dom->createElement('email');
            $email->appendChild($this->dom->createTextNode($values['email']));
            $author->appendChild($email);
        }

        if (isset($values['url'])) {
            $uri = $this->dom->createElement('uri');
            $uri->appendChild($this->dom->createTextNode($values['url']));
            $author->appendChild($uri);
        }

        $xml->appendChild($author);
    }
}
