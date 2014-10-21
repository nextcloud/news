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
    private $core;
    private $coreFactory;
    private $faviconFetcher;
    private $url;
    private $cacheDirectory;
    private $cacheDuration;
    private $time;
    private $item;
    private $purifier;
    private $fetchTimeout;
    private $proxyHost;
    private $getProxyPort;
    private $proxyAuth;
    private $config;
    private $appconfig;

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
        $this->core = $this->getMock(
            '\SimplePie_Core', [
                'set_timeout',
                'set_feed_url',
                'enable_cache',
                'set_stupidly_fast',
                'set_cache_location',
                'set_cache_duration',
                'set_proxyhost',
                'set_proxyport',
                'set_proxyuserpwd',
                'set_useragent',
                'init',
                'get_permalink',
                'get_items',
                'get_title',
                'get_image_url'
            ]);
        $this->coreFactory = $this->getMockBuilder(
            '\OCA\News\Utility\SimplePieAPIFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreFactory->expects($this->any())
            ->method('getCore')
            ->will($this->returnValue($this->core));
        $this->item = $this->getMockBuilder(
            '\SimplePie_Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->faviconFetcher = $this->getMockBuilder(
            '\OCA\News\Utility\FaviconFetcher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->appconfig = $this->getMockBuilder(
            '\OCA\News\Config\AppConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->time = 2323;
        $timeFactory = $this->getMock('TimeFactory', ['getTime']);
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->cacheDuration = 100;
        $this->cacheDirectory = 'dir/';
        $this->proxyHost = 'test';
        $this->proxyPort = 30;
        $this->proxyAuth = 'hi';
        $this->fetchTimeout = 40;
        $this->config = $this->getMockBuilder(
            '\OCA\News\Config\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->any())
            ->method('getSimplePieCacheDuration')
            ->will($this->returnValue($this->cacheDuration));
        $this->config->expects($this->any())
            ->method('getProxyHost')
            ->will($this->returnValue($this->proxyHost));
        $this->config->expects($this->any())
            ->method('getProxyAuth')
            ->will($this->returnValue($this->proxyAuth));
        $this->config->expects($this->any())
            ->method('getProxyPort')
            ->will($this->returnValue($this->proxyPort));
        $this->config->expects($this->any())
            ->method('getFeedFetcherTimeout')
            ->will($this->returnValue($this->fetchTimeout));
        $this->appconfig->expects($this->any())
            ->method('getConfig')
            ->with($this->equalTo('version'))
            ->will($this->returnValue(3));
        $this->fetcher = new FeedFetcher($this->coreFactory,
                         $this->faviconFetcher,
                         $timeFactory,
                         $this->cacheDirectory,
                         $this->config,
                         $this->appconfig);
        $this->url = 'http://tests';

        $this->permalink = 'http://permalink';
        $this->title = 'my&amp;lt;&apos; title';
        $this->guid = 'hey guid here';
        $this->body = 'let the bodies hit the floor <a href="test">test</a>';
        $this->body2 = 'let the bodies hit the floor <a target="_blank" href="test">test</a>';
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


    public function testDoesNotUseProxyIfNotEnabled() {
        $this->config->expects($this->any())
            ->method('getProxyHost')
            ->will($this->returnValue(''));
        $this->core->expects($this->never())
            ->method('set_proxyhost');
        $this->core->expects($this->never())
            ->method('set_proxyport');
        $this->core->expects($this->never())
            ->method('set_proxyuserpwd');
    }


    public function testFetchThrowsExceptionWhenInitFailed() {
        $this->core->expects($this->once())
            ->method('set_feed_url')
            ->with($this->equalTo($this->url));
        $this->core->expects($this->once())
            ->method('enable_cache')
            ->with($this->equalTo(true));
        $this->core->expects($this->once())
            ->method('set_timeout')
            ->with($this->equalTo($this->fetchTimeout));
        $this->core->expects($this->once())
            ->method('set_cache_location')
            ->with($this->equalTo($this->cacheDirectory));
        $this->core->expects($this->once())
            ->method('set_proxyhost')
            ->with($this->equalTo($this->proxyHost));
        $this->core->expects($this->once())
            ->method('set_proxyport')
            ->with($this->equalTo($this->proxyPort));
        $this->core->expects($this->once())
            ->method('set_proxyuserpwd')
            ->with($this->equalTo($this->proxyAuth));
        $this->core->expects($this->once())
            ->method('set_stupidly_fast')
            ->with($this->equalTo(true));
        $this->core->expects($this->once())
            ->method('set_cache_duration')
            ->with($this->equalTo($this->cacheDuration));
        $this->core->expects($this->once())
            ->method('set_useragent')
            ->with($this->equalTo(
                'ownCloud News/3 (+https://owncloud.org/; 1 subscriber; ' .
                    'feed-url=http://tests)'));
        $this->setExpectedException('\OCA\News\Fetcher\FetcherException');
        $this->fetcher->fetch($this->url);
    }


    public function testShouldCatchExceptionsAndThrowOwnException() {
        $this->core->expects($this->once())
            ->method('init')
            ->will($this->returnValue(true));
        $this->core->expects($this->once())
            ->method('get_items')
            ->will($this->throwException(new \Exception('oh noes!')));
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


    private function createItem($author=false, $enclosureType=null, $noPubDate=false) {
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
                ->method('fetch')
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
                ->method('fetch')
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
