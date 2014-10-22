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
    private $url;
    private $time;
    private $item;

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

    protected function setUp(){
        $this->reader = $this->getMockBuilder(
            '\PicoFeed\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->parser = $this->getMockBuilder(
            '\PicoFeed\Parser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this->getMockBuilder(
            '\PicoFeed\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->parsedFeed = $this->getMockBuilder(
            '\PicoFeed\Feed')
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->getMockBuilder(
            '\PicoFeed\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->faviconFetcher = $this->getMockBuilder(
            '\PicoFeed\Favicon')
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 2323;
        $timeFactory = $this->getMock('TimeFactory', ['getTime']);
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->fetcher = new FeedFetcher($this->reader,
                         $this->faviconFetcher,
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
    }


    public function testCanHandle(){
        $url = 'google.de';

        $this->assertTrue($this->fetcher->canHandle($url));
    }

    private function setUpReader($url='', $modified=true, $noParser=false,
                                 $noFeed=false) {
        $this->reader->expects($this->once())
            ->method('download')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->client));
        $this->client->expects($this->once())
            ->method('getLastModified')
            ->will($this->returnValue($this->modified));
        $this->client->expects($this->once())
            ->method('getEtag')
            ->will($this->returnValue($this->etag));

        if ($noParser) {
            $this->reader->expects($this->once())
                ->method('getParser')
                ->will($this->returnValue(false));
        } else {
            $this->reader->expects($this->once())
                ->method('getParser')
                ->will($this->returnValue($this->parser));

            if ($noFeed) {
                $this->parser->expects($this->once())
                    ->method('execute')
                    ->will($this->returnValue(false));
            } else {
                $this->parser->expects($this->once())
                    ->method('execute')
                    ->will($this->returnValue($this->parsedFeed));
            }
        }

        // uncomment if testing caching
        /*$this->client->expects($this->once())
            ->method('isModified')
            ->will($this->returnValue($modified));*/
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

        $this->expectItem('getDate', $this->pub);
        $item->setPubDate($this->pub);

        $item->setStatus(0);
        $item->setUnread();
        $item->setUrl($this->permalink);
        $item->setTitle('my<\' title');
        $item->setGuid($this->guid);
        $item->setGuidHash(md5($this->guid));
        $item->setBody($this->body);
        $item->setLastModified($this->time);

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
        $this->expectFeed('getUrl', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle('&its a title');
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);
        $feed->setLastModified($this->modified);
        $feed->setEtag($this->etag);

        if($hasFavicon) {
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
        $this->fetcher->fetch($this->url);
    }


    public function testFetchThrowsExceptionWhenParsingFailed() {
        $this->setUpReader($this->url, true, true, false);

        $this->setExpectedException('\OCA\News\Fetcher\FetcherException');
        $this->fetcher->fetch($this->url);
    }

    public function testFetch(){
        $this->setUpReader($this->url);
        $item = $this->createItem();
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testNoTitleUsesUrl(){
        $this->setUpReader($this->url);
        $this->expectFeed('getTitle', '');
        $this->expectFeed('getUrl', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle($this->url);
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);
        $feed->setFaviconLink(null);
        $feed->setLastModified($this->modified);
        $feed->setEtag($this->etag);

        $item = $this->createItem();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testAudioEnclosure(){
        $this->setUpReader($this->url);
        $item = $this->createItem('audio/ogg');
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testVideoEnclosure(){
        $this->setUpReader($this->url);
        $item = $this->createItem('video/ogg');
        $feed = $this->createFeed();
        $this->expectFeed('getItems', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

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
