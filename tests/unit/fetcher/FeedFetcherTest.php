<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Fetcher;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;


class FeedFetcherTest extends \PHPUnit_Framework_TestCase {

    private $fetcher;
    private $parser;
    private $reader;
    private $client;
    private $faviconFetcher;
    private $parsedFeed;
    private $faviconFactory;
    private $l10n;
    private $url;
    private $time;
    private $item;
    private $content;
    private $encoding;

    // items
    private $permalink;
    private $title;
    private $guid;
    private $pub;
    private $body;
    private $author;
    private $enclosureLink;

    // feed
    private $feedTitle;
    private $feedLink;
    private $feedImage;
    private $webFavicon;
    private $modified;
    private $etag;
    private $location;

    protected function setUp(){
        $this->l10n = $this->getMockBuilder(
            '\OCP\IL10N')
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = $this->getMockBuilder(
            '\PicoFeed\Reader\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->parser = $this->getMockBuilder(
            '\PicoFeed\Parser\Parser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this->getMockBuilder(
            '\PicoFeed\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->parsedFeed = $this->getMockBuilder(
            '\PicoFeed\Parser\Feed')
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->getMockBuilder(
            '\PicoFeed\Parser\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->faviconFetcher = $this->getMockBuilder(
            '\PicoFeed\Reader\Favicon')
            ->disableOriginalConstructor()
            ->getMock();
        $this->faviconFactory = $this->getMockBuilder(
            '\OCA\News\Utility\PicoFeedFaviconFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 2323;
        $timeFactory = $this->getMockBuilder(
            '\OCP\AppFramework\Utility\ITimeFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->fetcher = new FeedFetcher(
                        $this->reader,
                        $this->faviconFactory,
                        $this->l10n,
                        $timeFactory);
        $this->url = 'http://tests';

        $this->permalink = 'http://permalink';
        $this->title = 'my&amp;lt;&apos; title';
        $this->guid = 'hey guid here';
        $this->body = 'let the bodies hit the floor <a href="test">test</a>';
        $this->body2 = 'let the bodies hit the floor ' .
            '<a target="_blank" href="test">test</a>';
        $this->pub = 23111;
        $this->author = '&lt;boogieman';
        $this->enclosureLink = 'http://enclosure.you';

        $this->feedTitle = '&lt;a&gt;&amp;its a&lt;/a&gt; title';
        $this->feedLink = 'http://goatse';
        $this->feedImage = '/an/image';
        $this->webFavicon = 'http://anon.google.com';
        $this->authorMail = 'doe@joes.com';
        $this->modified = 3;
        $this->etag = 'yo';
        $this->content = 'some content';
        $this->encoding = 'UTF-8';
    }


    public function testCanHandle(){
        $url = 'google.de';

        $this->assertTrue($this->fetcher->canHandle($url));
    }

    private function setUpReader($url='', $modified=true, $noParser=false) {
        $this->reader->expects($this->once())
            ->method('discover')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->client));
        $this->client->expects($this->once())
            ->method('isModified')
            ->will($this->returnValue($modified));

        if (!$modified) {
            $this->reader->expects($this->never())
                ->method('getParser');
        } else {
            $this->client->expects($this->once())
                ->method('getLastModified')
                ->will($this->returnValue($this->modified));
            $this->client->expects($this->once())
                ->method('getEtag')
                ->will($this->returnValue($this->etag));
            $this->client->expects($this->once())
                ->method('getUrl')
                ->will($this->returnValue($this->location));
            $this->client->expects($this->once())
                ->method('getContent')
                ->will($this->returnValue($this->content));
            $this->client->expects($this->once())
                ->method('getEncoding')
                ->will($this->returnValue($this->encoding));

            if ($noParser) {
                $this->reader->expects($this->once())
                    ->method('getParser')
                    ->will($this->throwException(
                        new \PicoFeed\Reader\SubscriptionNotFoundException()
                    ));
            } else {
                $this->reader->expects($this->once())
                    ->method('getParser')
                    ->with(
                        $this->equalTo($this->location),
                        $this->equalTo($this->content),
                        $this->equalTo($this->encoding)
                    )
                    ->will($this->returnValue($this->parser));
            }

            $this->parser->expects($this->once())
                ->method('execute')
                ->will($this->returnValue($this->parsedFeed));
        }

    }


    private function expectFeed($method, $return, $count = 1) {
        $this->parsedFeed->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }

    private function expectItem($method, $return, $count = 1) {
        $this->item->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }


    private function createItem($enclosureType=null) {
        $this->expectItem('getUrl', $this->permalink);
        $this->expectItem('getTitle', $this->title);
        $this->expectItem('getId', $this->guid);
        $this->expectItem('getContent', $this->body);

        $item = new Item();

        date_default_timezone_set('America/Los_Angeles');
        $date = new \DateTime();
        $date->setTimestamp($this->pub);
        $this->expectItem('getDate', $date);
        $item->setPubDate($this->pub);

        $item->setStatus(0);
        $item->setUnread();
        $item->setUrl($this->permalink);
        $item->setTitle('my<\' title');
        $item->setGuid($this->guid);
        $item->setGuidHash($this->guid);
        $item->setBody($this->body);
        $item->setLastModified($this->time);
        $item->generateSearchIndex();

        $this->expectItem('getAuthor', $this->author);
        $item->setAuthor(html_entity_decode($this->author));

        if($enclosureType === 'audio/ogg' || $enclosureType === 'video/ogg') {
            $this->expectItem('getEnclosureUrl', $this->enclosureLink);
            $this->expectItem('getEnclosureType', $enclosureType);

            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->enclosureLink);
        }
        return $item;
    }


    private function createFeed($hasFavicon=false) {
        $this->expectFeed('getTitle', $this->feedTitle);
        $this->expectFeed('getSiteUrl', $this->feedLink);

        $feed = new Feed();
        $feed->setTitle('&its a title');
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);
        $feed->setLastModified($this->modified);
        $feed->setEtag($this->etag);
        $feed->setLocation($this->location);

        if($hasFavicon) {
            $this->faviconFactory->expects($this->once())
                ->method('build')
                ->will($this->returnValue($this->faviconFetcher));
            $this->faviconFetcher->expects($this->once())
                ->method('find')
                ->with($this->equalTo($this->feedLink))
                ->will($this->returnValue($this->webFavicon));
            $feed->setFaviconLink($this->webFavicon);
        }

        return $feed;
    }


    public function testFetchThrowsExceptionWhenFetchingFailed() {
        $this->setUpReader($this->url, true, false);

        $this->setExpectedException('\OCA\News\Fetcher\FetcherException');
        $this->fetcher->fetch($this->url, false);
    }


    public function testNoFetchIfNotModified(){
        $this->setUpReader($this->url, false);;
        $result = $this->fetcher->fetch($this->url, false);
    }

    public function testFetch(){
        $this->setUpReader($this->url);
        $item = $this->createItem();
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testAudioEnclosure(){
        $this->setUpReader($this->url);
        $item = $this->createItem('audio/ogg');
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testVideoEnclosure(){
        $this->setUpReader($this->url);
        $item = $this->createItem('video/ogg');
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }



    public function testFavicon() {
        $this->setUpReader($this->url);

        $feed = $this->createFeed(true);
        $item = $this->createItem();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFullText() {
        $this->setUpReader($this->url);

        $feed = $this->createFeed();
        $item = $this->createItem();
        $this->parser->expects($this->once())
            ->method('enableContentGrabber');
        $this->expectFeed('getItems', [$this->item]);
        $this->fetcher->fetch($this->url, false, null, null, true);
    }


    public function testNoFavicon() {
        $this->setUpReader($this->url);

        $feed = $this->createFeed(false);

        $this->faviconFetcher->expects($this->never())
                ->method('find');

        $item = $this->createItem();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


}
