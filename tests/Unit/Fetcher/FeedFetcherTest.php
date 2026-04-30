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

use DateTime;
use OCA\News\Fetcher\FaviconDiscovery;
use OCA\News\Vendor\FeedIo\Adapter\ResponseInterface;
use OCA\News\Vendor\FeedIo\Feed\Item\Author;
use OCA\News\Vendor\FeedIo\Feed\Item\MediaInterface;
use OCA\News\Vendor\FeedIo\Feed\Node\Category;
use OCA\News\Vendor\FeedIo\Feed\ItemInterface;
use OCA\News\Vendor\FeedIo\FeedInterface;
use OCA\News\Vendor\FeedIo\FeedIo;
use OCA\News\Vendor\FeedIo\Reader\Result;
use OC\L10N\L10N;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use OCA\News\Scraper\Scraper;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Config\FetcherConfig;

use OCA\News\Utility\Time;
use OCA\News\Utility\Cache;
use OCA\News\Utility\AppData;
use OCP\IL10N;
use OCP\ITempManager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
     * @var MockObject|FeedFetcher
     */
    private $fetcher;

    /**
     * Feed reader
     *
     * @var MockObject|FeedIo
     */
    private $reader;

    /**
     * Feed reader result
     *
     * @var MockObject|Result
     */
    private $result;

    /**
     * Feed reader result object
     *
     * @var MockObject|ResponseInterface
     */
    private $response;

    /**
     * @var MockObject|FaviconDiscovery
     */
    private $favicon;

    /**
     * @var MockObject|L10N
     */
    private $l10n;

    /**
     * @var MockObject|ItemInterface
     */
    private $item_mock;

    /**
     * @var MockObject|FeedInterface
     */
    private $feed_mock;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var MockObject|Scraper
     */
    private $scraper;

    /**
     * @var MockObject|FetcherConfig
     */
    private $fetcherConfig;

    /**
     * @var MockObject|Cache
     */
    private $cache;

    /**
     * @var MockObject|AppData
     */
    private $appData;

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
    private $fulltext_body;
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

    private $categories;
    private $categoriesJson;

    protected function setUp(): void
    {
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = $this->getMockBuilder(FeedIo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->favicon = $this->getMockBuilder(FaviconDiscovery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->item_mock = $this->getMockBuilder(ItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->feed_mock = $this->getMockBuilder(FeedInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 2323;
        $timeFactory = $this->getMockBuilder(Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->scraper = $this->getMockBuilder(Scraper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcherConfig = $this->getMockBuilder(FetcherConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appData = $this->getMockBuilder(AppData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher =  $this->getMockBuilder(FeedFetcher::class)
            ->setConstructorArgs([
                $this->reader,
                $this->favicon,
                $this->scraper,
                $this->l10n,
                $timeFactory,
                $this->logger,
                $this->fetcherConfig,
                $this->cache,
                $this->appData
            ])
            ->onlyMethods(['downloadFavicon'])
            ->getMock();
        $this->fetcher->method('downloadFavicon')
            ->willReturn('http://anon.google.com');
        $this->url = 'http://tests/';

        $this->permalink = 'http://permalink';
        $this->title = 'my&amp;lt;&apos; title';
        $this->guid = 'hey guid here';
        $this->guid_hash = 'df9a5f84e44bfe38cf44f6070d5b0250';
        $this->body
            = '<![CDATA[let the bodies hit the floor <a href="test">test</a>]]>';
        $this->parsed_body
            = 'let the bodies hit the floor <a href="test">test</a>';
        $this->fulltext_body
            = 'let the bodies hit the floor with full speed <a href="test">test</a>';
        $this->pub = 23111;
        $this->updated = 23444;
        $this->author = new Author();
        $this->author->setName('&lt;boogieman');
        $this->enclosure = 'http://enclosure.you';

        $category = new Category();
        $category->setLabel('food');
        $this->categories = new \ArrayIterator([$category]);
        $this->categoriesJson = json_encode(['food']);

        $this->feed_title = '&lt;a&gt;&amp;its a&lt;/a&gt; title';
        $this->feed_link = 'http://tests/';
        $this->feed_image = '/an/image';
        $this->web_favicon = 'http://anon.google.com';
        $this->modified = new DateTime('@3');
        $this->encoding = 'UTF-8';
        $this->language = 'de-DE';
        $this->rtl = false;
    }

    /**
     * Test if the fetcher can handle a URL.
     */
    public function testCanHandle()
    {
        $url = 'google.de';

        $this->assertTrue($this->fetcher->canHandle($url));
    }

    /**
     * Test if empty is logged when the feed remain the same.
     */
    public function testFetchIfNoModifiedExists()
    {
        $this->setUpReader($this->url, true);
        $item = $this->createItem();
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, '0', false, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Return body options
     *
     * @return array
     */
    public static function feedBodyProvider()
    {
        return [
            [
                '<![CDATA[let the bodies hit the floor <a href="test">test</a>]]>',
                'let the bodies hit the floor <a href="test">test</a>'
            ],
            [
                'let the bodies hit the floor <a href="test">test</a>',
                'let the bodies hit the floor <a href="test">test</a>'
            ],
            [
                'let the bodies hit the floor "test" test',
                'let the bodies hit the floor "test" test'
            ],
            [
                '<img src="https://imgs.xkcd.com/google_trends_maps.png" title="It\'s early 2020. The entire country is gripped with Marco Rubio" />',
                '<img src="https://imgs.xkcd.com/google_trends_maps.png" title="It\'s early 2020. The entire country is gripped with Marco Rubio" />'
            ],
        ];
    }

    /**
     * Test if body is set correctly.
     *
     * @dataProvider feedBodyProvider
     *
     * @param string $body        The body before parsing.
     * @param string $parsed_body The body after parsing.
     */
    public function testFetchWithFeedContent(string $body, string $parsed_body)
    {
        $bodyBackup = $this->body;
        $parsedBackup = $this->parsed_body;

        $this->body = $body;
        $this->parsed_body = $parsed_body;

        $this->setUpReader($this->url, true);
        $item = $this->createItem();
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, '0', false, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);

        $this->body = $bodyBackup;
        $this->parsed_body = $parsedBackup;
    }

    /**
     * Test if the fetch function fetches a feed.
     */
    public function testFetch()
    {
        $this->setUpReader($this->url);
        $item = $this->createItem();
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if fetching a feed with authentication works.
     */
    public function testFetchAccount()
    {
        $this->setUpReader('http://account%40email.com:F9sEU%2ARt%25%3AKFK8HMHT%26@tests/');
        $item = $this->createItem();
        $feed = $this->createFeed('de-DE', 'http://account%40email.com:F9sEU%2ARt%25%3AKFK8HMHT%26@tests/');
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch(
            $this->url,
            false,
            'account@email.com',
            'F9sEU*Rt%:KFK8HMHT&',
            null,
            []
        );

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if fetching a feed with an audio item works.
     */
    public function testAudioEnclosure()
    {
        $this->setUpReader($this->url);
        $item = $this->createItem('audio/ogg');
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if fetching a feed with a video item works.
     */
    public function testVideoEnclosure()
    {
        $this->setUpReader($this->url);
        $item = $this->createItem('video/ogg');
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if fetching a feed with a favicon works.
     */
    public function testFavicon()
    {
        $this->setUpReader($this->url);

        $feed = $this->createFeed('de-DE');
        $item = $this->createItem();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if fetching a feed with a non-western language works.
     */
    public function testRtl()
    {
        $this->setUpReader($this->url);
        $this->createFeed('he-IL');
        $this->createItem();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        list($_, $items) = $this->fetcher->fetch($this->url, false, null, null, null, []);
        $this->assertTrue($items[0]->getRtl());
    }

    /**
     * Test if fetching a feed with a RSS pubdate works and sets the property.
     */
    public function testRssPubDate()
    {
        $this->setUpReader($this->url);
        $this->createFeed('he-IL');
        $this->createItem();

        $this->item_mock->expects($this->exactly(3))
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        ['pubDate', '2018-03-27T19:50:29Z'],
                        ['published', null],
                    ]
                )
            );


        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null, []);
        $this->assertSame($items[0]->getPubDate(), 1522180229);
    }

    /**
     * Test if fetching a feed with a Atom pubdate works and sets the property.
     */
    public function testAtomPubDate()
    {
        $this->setUpReader($this->url);
        $this->createFeed('he-IL');
        $this->createItem();

        $this->item_mock->expects($this->exactly(4))
            ->method('getValue')
            ->will(
                $this->returnValueMap(
                    [
                        ['pubDate', null],
                        ['published', '2018-02-27T19:50:29Z'],
                    ]
                )
            );


        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null, []);
        $this->assertSame($items[0]->getPubDate(), 1519761029);
    }

    /**
     * Test if the fetch function fetches a feed that specifies a guid.
     */
    public function testFetchWithGuid()
    {
        $this->setUpReader($this->url);
        $this->createItem();
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);
        //Explicitly assert GUID value
        $this->assertEquals(2, count($result));
        $this->assertEquals(1, count($result[1]));
        $resultItem = $result[1][0];
        $this->assertEquals($this->guid, $resultItem->getGuid());
    }

    /**
     * Test if the fetch function fetches a feed that does not specify a guid.
     */
    public function testFetchWithoutGuid()
    {
        $this->setUpReader($this->url);
        $this->guid = null;
        $this->createItem();
        $feed = $this->createFeed();
        $this->mockIterator($this->feed_mock, [$this->item_mock]);
        $result = $this->fetcher->fetch($this->url, false, null, null, null, []);
        //Explicitly assert GUID value
        $this->assertEquals(2, count($result));
        $this->assertEquals(1, count($result[1]));
        $resultItem = $result[1][0];
        $this->assertEquals($this->permalink, $resultItem->getGuid());
    }

    /**
     * Test if the fetch function don't scrape the fulltext body if item does exist.
     */
    public function testFetchFulltextWhenItemExists()
    {
        $this->setUpReader($this->url);
        $this->scraper->expects($this->never())
            ->method('scrape')
            ->with($this->permalink);
        $this->scraper->expects($this->never())
            ->method('getContent');

        $guidHashList = [];
        $guidHashList[$this->guid_hash] = $this->modified->getTimestamp();

        $item = $this->createFulltextItem(true, false);
        $feed = $this->createFeed();

        $this->mockIterator($this->feed_mock, [$this->item_mock]);

        $result = $this->fetcher->fetch($this->url, true, null, null, null, $guidHashList);

        $this->assertEquals([$feed, []], $result);
    }

    /**
     * Test if the fetch function scrape the fulltext body if item does exist but has newer pub date.
     */
    public function testFetchFulltextWhenExistingItemIsUpdated()
    {
        $this->setUpReader($this->url);
        $this->scraper->expects($this->once())
            ->method('scrape')
            ->with($this->permalink)
            ->will($this->returnValue(true));
        $this->scraper->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->fulltext_body));

        $guidHashList[$this->guid_hash] = $this->modified->getTimestamp() - 1;

        $item = $this->createFulltextItem(false, false);
        $feed = $this->createFeed();

        $this->mockIterator($this->feed_mock, [$this->item_mock]);

        $result = $this->fetcher->fetch($this->url, true, null, null, null, $guidHashList);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if the fetch function scrape the fulltext body if item does not exist.
     */
    public function testFetchFulltextWhenItemIsNew()
    {
        $this->setUpReader($this->url);
        $this->scraper->expects($this->once())
            ->method('scrape')
            ->with($this->permalink)
            ->will($this->returnValue(true));
        $this->scraper->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->fulltext_body));

        $item = $this->createFulltextItem(false, false);
        $feed = $this->createFeed();

        $this->mockIterator($this->feed_mock, [$this->item_mock]);

        $result = $this->fetcher->fetch($this->url, true, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Test if the fetch function returns the feed item if the fulltext body is invalid
     */
    public function testFetchWhenFetchedFulltextBodyIsInvalid()
    {
        $this->setUpReader($this->url);
        $this->scraper->expects($this->once())
            ->method('scrape')
            ->with($this->permalink)
            ->will($this->returnValue(false));
        $this->scraper->expects($this->never())
            ->method('getContent');

        $item = $this->createFulltextItem(false, true);
        $feed = $this->createFeed();

        $this->mockIterator($this->feed_mock, [$this->item_mock]);

        $result = $this->fetcher->fetch($this->url, true, null, null, null, []);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Mock an iteration option on an existing mock
     *
     * @param object $iteratorMock The mock to enhance
     * @param array  $items        The items to make available
     *
     * @return mixed
     */
    private function mockIterator(object $iteratorMock, array $items)
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

    /**
     * Set up a FeedIO mock instance
     *
     * @param string      $url          URL that will be read.
     * @param string|null $modifiedDate Date of last fetch
     * @param bool        $modified     If the feed will be modified
     */
    private function setUpReader(string $url = '', bool $modified = true)
    {
        $this->reader->expects($this->once())
            ->method('read')
            ->with($url)
            ->will($this->returnValue($this->result));

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

    /**
     * Create an item mock.
     *
     * @param string|null $enclosureType Media type.
     *
     * @return Item
     */
    private function createItem($enclosureType = null)
    {
        $this->item_mock->expects($this->exactly(1))
            ->method('getLink')
            ->will($this->returnValue($this->permalink));
        $this->item_mock->expects($this->exactly(1))
            ->method('getTitle')
            ->will($this->returnValue($this->title));
        $this->item_mock->expects($this->exactly(1))
            ->method('getPublicId')
            ->will($this->returnValue($this->guid));
        $this->item_mock->expects($this->exactly(1))
            ->method('getContent')
            ->will($this->returnValue($this->body));
        $this->item_mock->expects($this->exactly(1))
            ->method('getLastModified')
            ->will($this->returnValue($this->modified));
        $this->item_mock->expects($this->exactly(1))
            ->method('getAuthor')
            ->will($this->returnValue($this->author));
        $this->item_mock->expects($this->exactly(1))
            ->method('getCategories')
            ->will($this->returnValue($this->categories));

        $item = new Item();

        $item->setUnread(true)
            ->setUrl($this->permalink)
            ->setTitle('my<\' title')
            ->setGuidHash($this->guid_hash)
            ->setBody($this->parsed_body)
            ->setRtl(false)
            ->setLastModified(3)
            ->setPubDate(3)
            ->setAuthor(html_entity_decode($this->author->getName()))
            ->setCategoriesJson($this->categoriesJson);

        // some tests deliberately omit this, so leave default value if the guid is to be ignored
        if ($this->guid !== null) {
            $item->setGuid($this->guid);
        }

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

            $this->item_mock->expects($this->exactly(1))
                ->method('hasMedia')
                ->will($this->returnValue(true));

            $this->item_mock->expects($this->exactly(1))
                ->method('getMedias')
                ->will($this->returnValue([$media, $media2]));

            $item->setEnclosureMime($enclosureType);
            $item->setEnclosureLink($this->enclosure);
        }
        $item->generateSearchIndex();

        return $item;
    }

    /**
     * Create a mock feed.
     *
     * @param string      $lang    Feed language.
     * @param bool        $favicon Fetch favicon.
     * @param string|null $url     Feed URL.
     *
     * @return Feed
     */
    private function createFeed($lang = 'de-DE', $url = null)
    {
        $url = $url ?? $this->url;
        $this->feed_mock->expects($this->exactly(3))
            ->method('getTitle')
            ->will($this->returnValue($this->feed_title));
        $this->feed_mock->expects($this->exactly(1))
            ->method('getLink')
            ->will($this->returnValue($this->feed_link));
        $this->feed_mock->expects($this->exactly(1))
            ->method('getLanguage')
            ->will($this->returnValue($lang));

        $feed = new Feed();

        $feed->setTitle('&its a title');
        $feed->setLink($this->feed_link);
        $feed->setLocation($this->location);
        $feed->setUrl($url);
        $feed->setAdded($this->time);
        $feed->setNextUpdateTime(0);

        $feed->setFaviconLink('http://anon.google.com');
        $this->favicon->expects($this->exactly(1))
            ->method('discover')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->web_favicon));


        return $feed;
    }

    /**
     * Create an fulltext item mock.
     *
     * @param bool   $itemExist
     * @param bool   $itemInvalid
     *
     * @return Item
     */
    private function createFulltextItem(bool $itemExist, bool $itemInvalid)
    {
        $this->item_mock->expects($this->exactly($itemExist ? 0 : 2))
            ->method('getLink')
            ->will($this->returnValue($this->permalink));
        $this->item_mock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->title));
        $this->item_mock->expects($this->any())
            ->method('getPublicId')
            ->will($this->returnValue($this->guid));
        $this->item_mock->expects($this->exactly($itemInvalid ? 1 : 0))
            ->method('getContent')
            ->will($this->returnValue($this->body));
        $this->item_mock->expects($this->any())
            ->method('getLastModified')
            ->will($this->returnValue($this->modified));
        $this->item_mock->expects($this->exactly($itemExist ? 0 : 1))
            ->method('getAuthor')
            ->will($this->returnValue($this->author));
        $this->item_mock->expects($this->exactly($itemExist ? 0 : 1))
            ->method('getCategories')
            ->will($this->returnValue($this->categories));

        $item = new Item();

        $item->setUnread(true)
            ->setUrl($this->permalink)
            ->setTitle('my<\' title')
            ->setGuid($this->guid)
            ->setGuidHash($this->guid_hash)
            ->setBody($itemInvalid ? $this->parsed_body : $this->fulltext_body)
            ->setRtl(false)
            ->setLastModified(3)
            ->setPubDate(3)
            ->setAuthor(html_entity_decode($this->author->getName()))
            ->setCategoriesJson($this->categoriesJson);

        $item->generateSearchIndex();

        return $item;
    }

    public function testHasLastModifiedHeaderReturnsTrueOn200WithHeader(): void
    {
        $iResponse = $this->createMock(\OCP\Http\Client\IResponse::class);
        $iResponse->method('getStatusCode')->willReturn(200);
        $iResponse->method('getHeader')->with('Last-Modified')->willReturn('Thu, 01 Jan 2026 00:00:00 GMT');

        $iClient = $this->createMock(\OCP\Http\Client\IClient::class);
        $iClient->method('head')->willReturn($iResponse);

        $this->fetcherConfig->method('getHttpClient')->willReturn($iClient);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');

        $this->assertTrue($this->fetcher->hasLastModifiedHeader('https://example.com'));
    }

    public function testHasLastModifiedHeaderReturnsFalseOn200WithoutHeader(): void
    {
        $iResponse = $this->createMock(\OCP\Http\Client\IResponse::class);
        $iResponse->method('getStatusCode')->willReturn(200);
        $iResponse->method('getHeader')->with('Last-Modified')->willReturn('');

        $iClient = $this->createMock(\OCP\Http\Client\IClient::class);
        $iClient->method('head')->willReturn($iResponse);

        $this->fetcherConfig->method('getHttpClient')->willReturn($iClient);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');

        $this->assertFalse($this->fetcher->hasLastModifiedHeader('https://example.com'));
    }

    public function testHasLastModifiedHeaderReturnsFalseOnErrorStatus(): void
    {
        $iResponse = $this->createMock(\OCP\Http\Client\IResponse::class);
        $iResponse->method('getStatusCode')->willReturn(404);
        // Header present on the error response — must still return false
        $iResponse->method('getHeader')->with('Last-Modified')->willReturn('Thu, 01 Jan 2026 00:00:00 GMT');

        $iClient = $this->createMock(\OCP\Http\Client\IClient::class);
        $iClient->method('head')->willReturn($iResponse);

        $this->fetcherConfig->method('getHttpClient')->willReturn($iClient);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');

        $this->assertFalse($this->fetcher->hasLastModifiedHeader('https://example.com'));
    }

    public function testHasLastModifiedHeaderReturnsFalseOnException(): void
    {
        $iClient = $this->createMock(\OCP\Http\Client\IClient::class);
        $iClient->method('head')->willThrowException(new \Exception('timeout'));

        $this->fetcherConfig->method('getHttpClient')->willReturn($iClient);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');

        $this->logger->expects($this->once())->method('warning');

        $this->assertFalse($this->fetcher->hasLastModifiedHeader('https://example.com'));
    }
}
