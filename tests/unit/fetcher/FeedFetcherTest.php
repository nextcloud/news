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
    private $authorMail;
    private $enclosureLink;

    // feed
    private $feedTitle;
    private $feedLink;
    private $feedImage;
    private $webFavicon;

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
    }


    public function testCanHandle(){
        $url = 'google.de';

        $this->assertTrue($this->fetcher->canHandle($url));
    }

    private function setUpReader($url='', $modified=true) {
        $this->reader->expects($this->once())
            ->method('download')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->client));
        $this->client->expects($this->once())
            ->method('getLastModified')
            ->with()
            ->will($this->returnValue($modified));
    }


    public function testFetchThrowsExceptionWhenFetchingFailed() {
        $this->setUpReader($this->url);
        $this->reader->expects($this->once())
            ->method('getParser')
            ->will($this->returnValue(false));

        $this->setExpectedException('\OCA\News\Fetcher\FetcherException');
        $this->fetcher->fetch($this->url);
    }


    public function testFetchThrowsExceptionWhenParsingFailed() {
        $this->setUpReader($this->url);
        $this->reader->expects($this->once())
            ->method('getParser')
            ->will($this->returnValue(false));

        $this->setExpectedException('\OCA\News\Fetcher\FetcherException');
        $this->fetcher->fetch($this->url);
    }


    private function expectCore($method, $return, $count = 1) {
        $this->core->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }

    private function expectItem($method, $return, $count = 1) {
        $this->item->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }


    private function createItem($author=false, $enclosureType=null,
                                $noPubDate=false) {
        $this->expectItem('get_permalink', $this->permalink);
        $this->expectItem('get_title', $this->title);
        $this->expectItem('get_id', $this->guid);
        $this->expectItem('get_content', $this->body);

        $item = new Item();

        if($noPubDate) {
            $this->expectItem('get_date', 0);
            $item->setPubDate($this->time);
        } else {
            $this->expectItem('get_date', $this->pub);
            $item->setPubDate($this->pub);
        }

        $item->setStatus(0);
        $item->setUnread();
        $item->setUrl($this->permalink);
        $item->setTitle('my<\' title');
        $item->setGuid($this->guid);
        $item->setGuidHash(md5($this->guid));
        $item->setBody($this->body);
        $item->setLastModified($this->time);
        if($author) {
            $mock = $this->getMock('author', ['get_name']);
            $mock->expects($this->once())
                ->method('get_name')
                ->will($this->returnValue($this->author));
            $this->expectItem('get_author', $mock);
            $item->setAuthor(html_entity_decode($this->author));
        } else {
            $mock = $this->getMock('author', ['get_name', 'get_email']);
            $mock->expects($this->any())
                ->method('get_name')
                ->will($this->returnValue(''));
            $mock->expects($this->any())
                ->method('get_email')
                ->will($this->returnValue($this->authorMail));

            $this->expectItem('get_author', $mock);
            $item->setAuthor(html_entity_decode($this->authorMail));
        }

        if($enclosureType === 'audio/ogg') {
            $mock = $this->getMock('enclosure', ['get_type', 'get_link']);
            $mock->expects($this->any())
                ->method('get_type')
                ->will($this->returnValue($enclosureType));
            $mock->expects($this->any())
                ->method('get_link')
                ->will($this->returnValue($this->enclosureLink));
            $this->expectItem('get_enclosure', $mock);
            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->enclosureLink);
        } elseif ($enclosureType === 'video/ogg') {
            $mock = $this->getMock('enclosure', ['get_type', 'get_link']);
            $mock->expects($this->any())
                ->method('get_type')
                ->will($this->returnValue($enclosureType));
            $mock->expects($this->any())
                ->method('get_link')
                ->will($this->returnValue($this->enclosureLink));
            $this->expectItem('get_enclosure', $mock);
            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->enclosureLink);
        }
        return $item;
    }


    private function createFeed($hasFeedFavicon=false, $hasWebFavicon=false) {
        $this->expectCore('get_title', $this->feedTitle);
        $this->expectCore('get_permalink', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle('&its a title');
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);

        if($hasWebFavicon) {
            $this->faviconFetcher->expects($this->once())
                ->method('find')
                ->with($this->equalTo($this->feedLink))
                ->will($this->returnValue($this->webFavicon));
            $feed->setFaviconLink($this->webFavicon);
        }

        if($hasFeedFavicon) {
            $this->expectCore('get_image_url', $this->feedImage);
            $feed->setFaviconLink($this->feedImage);
        } elseif(!$hasWebFavicon) {
            $feed->setFaviconLink(null);
            $this->expectCore('get_image_url', null);
        }


        return $feed;
    }


    public function testFetchMapItems(){
        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $item = $this->createItem(false, 'audio/ogg');
        $feed = $this->createFeed();
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testFetchMapItemsNoFeedTitleUsesUrl(){
        $this->expectCore('get_title', '');
        $this->expectCore('get_permalink', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle($this->url);
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);
        $feed->setFaviconLink(null);

        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $item = $this->createItem();
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFetchMapItemsAuthorExists(){
        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $item = $this->createItem(true);
        $feed = $this->createFeed(true);
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testFetchMapItemsEnclosureExists(){
        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $item = $this->createItem(false, true);
        $feed = $this->createFeed(false, true);
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testFetchMapItemsNoPubdate(){
        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $item = $this->createItem(false, true, true);
        $feed = $this->createFeed(false, true);
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testFetchMapItemsGetFavicon() {
        $this->expectCore('get_title', $this->feedTitle);
        $this->expectCore('get_permalink', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle('&its a title');
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);
        $feed->setFaviconLink($this->webFavicon);

        $this->core->expects($this->once())
                ->method('init')
                ->will($this->returnValue(true));

        $this->faviconFetcher->expects($this->once())
                ->method('find')
                ->will($this->returnValue($this->webFavicon));

        $item = $this->createItem(false, 'video/ogg');
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFetchMapItemsNoGetFavicon() {
        $this->expectCore('get_title', $this->feedTitle);
        $this->expectCore('get_permalink', $this->feedLink, 2);

        $feed = new Feed();
        $feed->setTitle('&its a title');
        $feed->setUrl($this->url);
        $feed->setLink($this->feedLink);
        $feed->setAdded($this->time);

        $this->core->expects($this->once())
                ->method('init')
                ->will($this->returnValue(true));

        $this->faviconFetcher->expects($this->never())
                ->method('fetch');

        $item = $this->createItem(false, true);
        $this->expectCore('get_items', [$this->item]);
        $result = $this->fetcher->fetch($this->url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


}
