<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\Fetcher;

use FeedIo\Feed\Item\Author;
use FeedIo\Feed\Item\MediaInterface;
use FeedIo\Feed\ItemInterface;
use FeedIo\FeedInterface;
use Favicon\Favicon;
use OC\L10N\L10N;
use OCA\AdminAudit\Actions\Auth;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Utility\PsrLogger;
use OCA\News\Utility\Time;
use OCP\IL10N;

use PHPUnit\Framework\TestCase;

/**
 * Class FeedFetcherTest
 *
 * @package OCA\News\Tests\Unit\Fetcher
 */
class FeedFetcherTest extends TestCase
{
    /**
     * The class to test
     *
     * @var FeedFetcher
     */
    private $fetcher;

    /**
     * Feed reader
     *
     * @var \FeedIo\FeedIo
     */
    private $reader;

    /**
     * Feed reader result
     *
     * @var \FeedIo\Reader\Result
     */
    private $result;

    /**
     * Feed reader result object
     *
     * @var \FeedIo\Adapter\ResponseInterface
     */
    private $response;

    /**
     * @var \Favicon\Favicon
     */
    private $favicon;

    /**
     * @var L10N
     */
    private $l10n;

    /**
     * @var ItemInterface
     */
    private $item_mock;

    /**
     * @var FeedInterface
     */
    private $feed_mock;

    /**
     * @var PsrLogger
     */
    private $logger;

    //metadata
    /**
     * @var integer
     */
    private $time;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var string
     */
    private $url;

    // items
    private $permalink;
    private $title;
    private $guid;
    private $guid_hash;
    private $pub;
    private $updated;
    private $body;
    private $parsed_body;
    /**
     * @var Author
     */
    private $author;
    private $enclosure;
    private $rtl;
    private $language;

    // feed
    private $feed_title;
    private $feed_link;
    private $feed_image;
    private $web_favicon;
    private $modified;
    private $location;

    protected function setUp()
    {
        $this->l10n     = $this->getMockBuilder(\OCP\IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader   = $this->getMockBuilder(\FeedIo\FeedIo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->favicon  = $this->getMockBuilder(\Favicon\Favicon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->result   = $this->getMockBuilder(\FeedIo\Reader\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder(\FeedIo\Adapter\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item_mock = $this->getMockBuilder(\FeedIo\Feed\ItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->feed_mock = $this->getMockBuilder(\FeedIo\FeedInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 2323;
        $timeFactory = $this->getMockBuilder(\OCA\News\Utility\Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->logger  = $this->getMockBuilder(PsrLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher = new FeedFetcher(
            $this->reader,
            $this->favicon,
            $this->l10n,
            $timeFactory,
            $this->logger
        );
        $this->url     = 'http://tests/';

        $this->permalink   = 'http://permalink';
        $this->title       = 'my&amp;lt;&apos; title';
        $this->guid        = 'hey guid here';
        $this->guid_hash   = 'df9a5f84e44bfe38cf44f6070d5b0250';
        $this->body        = '<![CDATA[let the bodies hit the floor <a href="test">test</a>]]>';
        $this->parsed_body = 'let the bodies hit the floor <a href="test">test</a>';
        $this->pub         = 23111;
        $this->updated     = 23444;
        $this->author      = new Author();
        $this->author->setName('&lt;boogieman');
        $this->enclosure   = 'http://enclosure.you';

        $this->feed_title  = '&lt;a&gt;&amp;its a&lt;/a&gt; title';
        $this->feed_link   = 'http://tests/';
        $this->feed_image  = '/an/image';
        $this->web_favicon = 'http://anon.google.com';
        $this->modified    = $this->getMockBuilder('\DateTime')->getMock();
        $this->modified->expects($this->any())
            ->method('getTimestamp')
            ->will($this->returnValue(3));
        $this->encoding   = 'UTF-8';
        $this->language   = 'de-DE';
        $this->rtl        = false;
    }

    public function testCanHandle()
    {
        $url = 'google.de';

        $this->assertTrue($this->fetcher->canHandle($url));
    }

    /**
     * Test if empty is logged when the feed remain the same.
     */
    public function testNoFetchIfNotModified()
    {
        $this->__setUpReader($this->url, false);
        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Feed {url} was not modified since last fetch. old: {old}, new: {new}');
        $result = $this->fetcher->fetch($this->url, false, null, null, null);
        $this->assertSame([null, []], $result);
    }

    /**
     * Test if feed is updated when lastModified is 0.
     */
    public function testLastModifiedIsEmptyFetch()
    {
        $this->__setUpReader($this->url);
        $item = $this->_createItem();
        $feed = $this->_createFeed('de-DE', false, null, '0');
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, '0', null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFetch()
    {
        $this->__setUpReader($this->url);
        $item = $this->_createItem();
        $feed = $this->_createFeed();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFetchAccount()
    {
        $this->__setUpReader('http://account%40email.com:F9sEU%2ARt%25%3AKFK8HMHT%26@tests/');
        $item = $this->_createItem();
        $feed = $this->_createFeed('de-DE', false, 'http://account%40email.com:F9sEU%2ARt%25%3AKFK8HMHT%26@tests/');
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, 'account@email.com', 'F9sEU*Rt%:KFK8HMHT&');

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testAudioEnclosure()
    {
        $this->__setUpReader($this->url);
        $item = $this->_createItem('audio/ogg');
        $feed = $this->_createFeed();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testVideoEnclosure()
    {
        $this->__setUpReader($this->url);
        $item = $this->_createItem('video/ogg');
        $feed = $this->_createFeed();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFavicon() 
    {
        $this->__setUpReader($this->url);

        $feed = $this->_createFeed('de-DE', true);
        $item = $this->_createItem();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, true, null, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testNoFavicon() 
    {
        $this->__setUpReader($this->url);

        $feed = $this->_createFeed(false);

        $this->favicon->expects($this->never())
            ->method('get');

        $item = $this->_createItem();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testRtl() 
    {
        $this->__setUpReader($this->url);
        $this->_createFeed('he-IL');
        $this->_createItem();
        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
        $this->assertTrue($items[0]->getRtl());
    }

    public function testRssPubDate()
    {
        $this->__setUpReader($this->url);
        $this->_createFeed('he-IL');
        $this->_createItem();

        $this->item_mock->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap([
                ['pubDate', '2018-03-27T19:50:29Z'],
                ['published', NULL],
            ]));


        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
        $this->assertSame($items[0]->getPubDate(), 1522180229);
    }

    public function testAtomPubDate()
    {
        $this->__setUpReader($this->url);
        $this->_createFeed('he-IL');
        $this->_createItem();

        $this->item_mock->expects($this->exactly(3))
                         ->method('getValue')
                         ->will($this->returnValueMap([
                             ['pubDate', NULL],
                             ['published', '2018-02-27T19:50:29Z'],
                         ]));


        $this->_mockIterator($this->feed_mock, [$this->item_mock]);
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
        $this->assertSame($items[0]->getPubDate(), 1519761029);
    }

    /**
     * Mock an iteration option on an existing mock
     *
     * @param object $iteratorMock The mock to enhance
     * @param array  $items        The items to make available
     *
     * @return mixed
     */
    private function _mockIterator($iteratorMock, array $items)
    {
        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return sizeof($iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }

    private function __setUpReader($url = '', $modified = true)
    {
        $this->reader->expects($this->once())
            ->method('read')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->result));
        $this->result->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->response->expects($this->once())
            ->method('isModified')
            ->will($this->returnValue($modified));
        $this->location = $url;

        if (!$modified) {
            $this->result->expects($this->never())
                ->method('getUrl');
        } else {
            $this->result->expects($this->once())
                ->method('getUrl')
                ->will($this->returnValue($this->location));
            $this->result->expects($this->once())
                ->method('getFeed')
                ->will($this->returnValue($this->feed_mock));
        }

    }

    private function _expectFeed($method, $return, $count = 1)
    {
        $this->feed_mock->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }

    private function _expectItem($method, $return, $count = 1)
    {
        $this->item_mock->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }


    private function _createItem($enclosureType=null)
    {
        $this->_expectItem('getLink', $this->permalink);
        $this->_expectItem('getTitle', $this->title);
        $this->_expectItem('getPublicId', $this->guid);
        $this->_expectItem('getDescription', $this->body);
        $this->_expectItem('getLastModified', $this->modified);
        $this->_expectItem('getAuthor', $this->author);

        $item = new Item();

        $item->setStatus(0);
        $item->setUnread(true);
        $item->setUrl($this->permalink);
        $item->setTitle('my<\' title');
        $item->setGuid($this->guid);
        $item->setGuidHash($this->guid_hash);
        $item->setBody($this->parsed_body);
        $item->setRtl(false);
        $item->setLastModified(3);
        $item->setPubDate(3);
        $item->setAuthor(html_entity_decode($this->author->getName()));

        if ($enclosureType === 'audio/ogg' || $enclosureType === 'video/ogg') {
            $media = $this->getMockbuilder(MediaInterface::class)->getMock();
            $media->expects($this->once())
                ->method('getType')
                ->will($this->returnValue('sounds'));
            $media2 = $this->getMockbuilder(MediaInterface::class)->getMock();
            $media2->expects($this->exactly(2))
                ->method('getType')
                ->will($this->returnValue($enclosureType));
            $media2->expects($this->once())
                ->method('getUrl')
                ->will($this->returnValue($this->enclosure));
            $this->_expectItem('hasMedia', true);
            $this->_expectItem('getMedias', [$media, $media2]);

            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->enclosure);
        }
        $item->generateSearchIndex();

        return $item;
    }


    private function _createFeed($lang='de-DE', $favicon=false, $url=null, $lastModified=3)
    {
        $url = $url ?? $this->url;
        $this->_expectFeed('getTitle', $this->feed_title, 2);
        $this->_expectFeed('getLink', $this->feed_link);
        $this->_expectFeed('getLastModified', $this->modified);
        $this->_expectFeed('getLanguage', $lang);

        $feed = new Feed();

        $feed->setTitle('&its a title');
        $feed->setLink($this->feed_link);
        $feed->setLocation($this->location);
        $feed->setUrl($url);
        $feed->setLastModified($lastModified);
        $feed->setAdded($this->time);
        if ($favicon) {
            $feed->setFaviconLink('http://anon.google.com');
            $this->favicon->expects($this->exactly(1))
                ->method('get')
                ->with($this->equalTo($this->feed_link))
                ->will($this->returnValue($this->web_favicon));
        } else {
            $this->favicon->expects($this->never())
                ->method('get');
        }

        return $feed;
    }
}
