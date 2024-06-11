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
use Favicon\Favicon;
use FeedIo\Adapter\ResponseInterface;
use FeedIo\Feed\Item\Author;
use FeedIo\Feed\Item\MediaInterface;
use FeedIo\Feed\Node\Category;
use FeedIo\Feed\ItemInterface;
use FeedIo\FeedInterface;
use FeedIo\FeedIo;
use FeedIo\Reader\Result;
use OC\L10N\L10N;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use OCA\News\Scraper\Scraper;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Config\FetcherConfig;

use OCA\News\Utility\Time;
use OCA\News\Utility\Cache;
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
     * @var MockObject|Favicon
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
        $this->favicon = $this->getMockBuilder(Favicon::class)
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
        $this->fetcher = new FeedFetcher(
            $this->reader,
            $this->favicon,
            $this->scraper,
            $this->l10n,
            $timeFactory,
            $this->logger,
            $this->fetcherConfig,
            $this->cache
        );
        $this->url = 'http://tests/';

        $this->permalink = 'http://permalink';
        $this->title = 'my&amp;lt;&apos; title';
        $this->guid = 'hey guid here';
        $this->guid_hash = 'df9a5f84e44bfe38cf44f6070d5b0250';
        $this->body
            = '<![CDATA[let the bodies hit the floor <a href="test">test</a>]]>';
        $this->parsed_body
            = 'let the bodies hit the floor <a href="test">test</a>';
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
        $result = $this->fetcher->fetch($this->url, '0', false, null, null);

        $this->assertEquals([$feed, [$item]], $result);
    }

    /**
     * Return body options
     *
     * @return array
     */
    public function feedBodyProvider()
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
        $result = $this->fetcher->fetch($this->url, '0', false, null, null);

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
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

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
            null
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
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

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
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

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
        $result = $this->fetcher->fetch($this->url, false, null, null, null);

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
        list($_, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
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
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
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
        list($feed, $items) = $this->fetcher->fetch($this->url, false, null, null, null);
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
        $result = $this->fetcher->fetch($this->url, false, null, null. null, null);
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
        $result = $this->fetcher->fetch($this->url, false, null, null, null);
        //Explicitly assert GUID value
        $this->assertEquals(2, count($result));
        $this->assertEquals(1, count($result[1]));
        $resultItem = $result[1][0];
        $this->assertEquals($this->permalink, $resultItem->getGuid());
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

        $feed->setFaviconLink('http://anon.google.com');
        $this->favicon->expects($this->exactly(1))
            ->method('get')
            ->with($this->equalTo($url))
            ->will($this->returnValue($this->web_favicon));


        return $feed;
    }
}
