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

use FeedIo\Feed\Item\MediaInterface;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Utility\PicoFeedFaviconFactory;
use OCA\News\Utility\Time;
use OCP\Http\Client\IClientService;
use OCP\IL10N;

use PHPUnit\Framework\TestCase;
use PicoFeed\Client\Client;
use PicoFeed\Parser\Parser;
use PicoFeed\Processor\ItemPostProcessor;
use PicoFeed\Reader\Favicon;
use PicoFeed\Reader\Reader;

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
    private $_fetcher;

    /**
     * Feed reader
     *
     * @var \FeedIo\FeedIo
     */
    private $_reader;

    /**
     * Feed reader result
     *
     * @var \FeedIo\Reader\Result
     */
    private $_result;

    /**
     * Feed reader result object
     *
     * @var \FeedIo\Adapter\ResponseInterface
     */
    private $_response;

    private $_favicon;
    private $_l10n;
    private $_url;
    private $_time;
    private $_item_mock;
    private $_feed_mock;
    private $_encoding;

    // items
    private $_permalink;
    private $_title;
    private $_guid;
    private $_pub;
    private $_updated;
    private $_body;
    private $_author;
    private $_enclosure;
    private $_rtl;
    private $_language;

    // feed
    private $_feed_title;
    private $_feed_link;
    private $_feed_image;
    private $_web_favicon;
    private $_modified;
    private $_location;

    protected function setUp()
    {
        $this->_l10n     = $this->getMockBuilder(\OCP\IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_reader   = $this->getMockBuilder(\FeedIo\FeedIo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_favicon  = $this->getMockBuilder(\Favicon\Favicon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_result   = $this->getMockBuilder(\FeedIo\Reader\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_response = $this->getMockBuilder(\FeedIo\Adapter\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_item_mock = $this->getMockBuilder(\FeedIo\Feed\ItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_feed_mock = $this->getMockBuilder(\FeedIo\FeedInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_time = 2323;
        $timeFactory = $this->getMockBuilder(\OCA\News\Utility\Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->_time));
        $clientService  = $this->getMockBuilder(IClientService::class)
            ->getMock();
        $this->_fetcher = new FeedFetcher(
            $this->_reader,
            $this->_favicon,
            $this->_l10n,
            $timeFactory,
            $clientService
        );
        $this->_url     = 'http://tests';

        $this->_permalink = 'http://permalink';
        $this->_title     = 'my&amp;lt;&apos; title';
        $this->_guid      = 'hey guid here';
        $this->_body      = 'let the bodies hit the floor <a href="test">test</a>';
        $this->_pub       = 23111;
        $this->_updated   = 23444;
        $this->_author    = '&lt;boogieman';
        $this->_enclosure = 'http://enclosure.you';

        $this->_feed_title  = '&lt;a&gt;&amp;its a&lt;/a&gt; title';
        $this->_feed_link   = 'http://tests';
        $this->_feed_image  = '/an/image';
        $this->_web_favicon = 'http://anon.google.com';
        $this->_modified    = $this->getMockBuilder('\DateTime')->getMock();
        $this->_modified->expects($this->any())
            ->method('getTimestamp')
            ->will($this->returnValue(3));
        $this->_encoding   = 'UTF-8';
        $this->_language   = 'de-DE';
        $this->_rtl        = false;
    }

    public function testCanHandle()
    {
        $url = 'google.de';

        $this->assertTrue($this->_fetcher->canHandle($url));
    }

    public function testNoFetchIfNotModified()
    {
        $this->_setUpReader($this->_url, false);;
        $result = $this->_fetcher->fetch($this->_url, false);
        $this->assertSame([null, null], $result);
    }

    public function testFetch()
    {
        $this->_setUpReader($this->_url);
        $item = $this->_createItem();
        $feed = $this->_createFeed();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        $result = $this->_fetcher->fetch($this->_url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testAudioEnclosure()
    {
        $this->_setUpReader($this->_url);
        $item = $this->_createItem('audio/ogg');
        $feed = $this->_createFeed();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        $result = $this->_fetcher->fetch($this->_url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }


    public function testVideoEnclosure()
    {
        $this->_setUpReader($this->_url);
        $item = $this->_createItem('video/ogg');
        $feed = $this->_createFeed();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        $result = $this->_fetcher->fetch($this->_url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testFavicon() 
    {
        $this->_setUpReader($this->_url);

        $feed = $this->_createFeed('de-DE', true);
        $item = $this->_createItem();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        $result = $this->_fetcher->fetch($this->_url, true);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testNoFavicon() 
    {
        $this->_setUpReader($this->_url);

        $feed = $this->_createFeed(false);

        $this->_favicon->expects($this->never())
            ->method('get');

        $item = $this->_createItem();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        $result = $this->_fetcher->fetch($this->_url, false);

        $this->assertEquals([$feed, [$item]], $result);
    }

    public function testRtl() 
    {
        $this->_setUpReader($this->_url);
        $this->_createFeed('he-IL');
        $this->_createItem();
        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        list($feed, $items) = $this->_fetcher->fetch(
            $this->_url, false
        );
        $this->assertTrue($items[0]->getRtl());
    }

    public function testRssPubDate()
    {
        $this->_setUpReader($this->_url);
        $this->_createFeed('he-IL');
        $this->_createItem();

        $this->_item_mock->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap([
                ['pubDate', '2018-03-27T19:50:29Z'],
                ['published', NULL],
            ]));


        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        list($feed, $items) = $this->_fetcher->fetch($this->_url, false);
        $this->assertSame($items[0]->getPubDate(), 1522180229);
    }

    public function testAtomPubDate()
    {
        $this->_setUpReader($this->_url);
        $this->_createFeed('he-IL');
        $this->_createItem();

        $this->_item_mock->expects($this->exactly(3))
                         ->method('getValue')
                         ->will($this->returnValueMap([
                             ['pubDate', NULL],
                             ['published', '2018-02-27T19:50:29Z'],
                         ]));


        $this->_mockIterator($this->_feed_mock, [$this->_item_mock]);
        list($feed, $items) = $this->_fetcher->fetch($this->_url, false);
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

    private function _setUpReader($url='', $modified=true)
    {
        $this->_reader->expects($this->once())
            ->method('readSince')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->_result));
        $this->_result->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->_response));
        $this->_response->expects($this->once())
            ->method('isModified')
            ->will($this->returnValue($modified));

        if (!$modified) {
            $this->_result->expects($this->never())
                ->method('getUrl');
        } else {
            $this->_result->expects($this->once())
                ->method('getUrl')
                ->will($this->returnValue($this->_location));
            $this->_result->expects($this->once())
                ->method('getFeed')
                ->will($this->returnValue($this->_feed_mock));
        }

    }

    private function _expectFeed($method, $return, $count = 1)
    {
        $this->_feed_mock->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }

    private function _expectItem($method, $return, $count = 1)
    {
        $this->_item_mock->expects($this->exactly($count))
            ->method($method)
            ->will($this->returnValue($return));
    }


    private function _createItem($enclosureType=null)
    {
        $this->_expectItem('getLink', $this->_permalink);
        $this->_expectItem('getTitle', $this->_title);
        $this->_expectItem('getPublicId', $this->_guid);
        $this->_expectItem('getDescription', $this->_body);
        $this->_expectItem('getLastModified', $this->_modified, 2);
        $this->_expectItem('getAuthor', $this->_author);

        $item = new Item();

        $item->setStatus(0);
        $item->setUnread(true);
        $item->setUrl($this->_permalink);
        $item->setTitle('my<\' title');
        $item->setGuid($this->_guid);
        $item->setGuidHash($this->_guid);
        $item->setBody($this->_body);
        $item->setRtl(false);
        $item->setLastModified(3);
        $item->setPubDate(3);
        $item->setAuthor(html_entity_decode($this->_author));

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
                ->will($this->returnValue($this->_enclosure));
            $this->_expectItem('hasMedia', true);
            $this->_expectItem('getMedias', [$media, $media2]);

            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->_enclosure);
        }
        $item->generateSearchIndex();

        return $item;
    }


    private function _createFeed($lang='de-DE', $favicon=false)
    {
        $this->_expectFeed('getTitle', $this->_feed_title);
        $this->_expectFeed('getLink', $this->_feed_link);
        $this->_expectFeed('getLastModified', $this->_modified);
        $this->_expectFeed('getLanguage', $lang);

        $feed = new Feed();

        $feed->setTitle('&its a title');
        $feed->setLink($this->_feed_link);
        $feed->setUrl($this->_url);
        $feed->setLastModified(3);
        $feed->setAdded($this->_time);
        if ($favicon) {
            $feed->setFaviconLink('http://anon.google.com');
            $this->_favicon->expects($this->exactly(1))
                ->method('get')
                ->with($this->equalTo($this->_feed_link))
                ->will($this->returnValue($this->_web_favicon));
        } else {
            $this->_favicon->expects($this->never())
                ->method('get');
        }

        return $feed;
    }
}
