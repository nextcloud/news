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


namespace OCA\News\Tests\Unit\Service;

use FeedIo\Explorer;
use FeedIo\Reader\ReadErrorException;

use OCA\News\Db\FeedMapperV2;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FeedServiceTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedMapperV2
     */
    private $mapper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;

    /** @var FeedServiceV2 */
    private $class;

    /**
     * @var string
     */
    private $uid;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedFetcher
     */
    private $fetcher;

    /**
     * @var int
     */
    private $time;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\HTMLPurifier
     */
    private $purifier;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Explorer
     */
    private $explorer;

    private $response;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->time = 222;
        $timeFactory = $this->getMockBuilder(Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));

        $this->mapper = $this
            ->getMockBuilder(FeedMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher = $this
            ->getMockBuilder(FeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->explorer = $this
            ->getMockBuilder(Explorer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this
            ->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purifier = $this
            ->getMockBuilder(\HTMLPurifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->class = new FeedServiceV2(
            $this->mapper,
            $this->fetcher,
            $this->itemService,
            $this->explorer,
            $this->purifier,
            $this->logger
        );
        $this->uid = 'jack';
    }

    /**
     * @covers \OCA\News\Service\FeedServiceV2::findAll
     */
    public function testFindAll()
    {
        $this->response = [];
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->uid)
            ->will($this->returnValue([]));

        $result = $this->class->findAllForUser($this->uid);
        $this->assertEquals([], $result);
    }


    public function testCreateDoesNotFindFeed()
    {
        $url = 'test';

        $this->fetcher->expects($this->exactly(2))
            ->method('fetch')
            ->with($url)
            ->will($this->throwException(new ReadErrorException('There is no feed')));

        $this->expectException(ServiceNotFoundException::class);
        $this->class->create($this->uid, $url, 1);
    }

    public function testCreate()
    {
        $url = 'http://test';
        $folderId = 10;
        $createdFeed = new Feed();
        $createdFeed->setUrl($url);
        $createdFeed->setUrlHash('hsssi');
        $createdFeed->setLink($url);
        $createdFeed->setTitle('hehoy');
        $createdFeed->setBasicAuthUser('user');
        $createdFeed->setBasicAuthPassword('pass');
        $item1 = new Item();
        $item1->setFeedId(4);
        $item1->setGuidHash('hi');
        $item2 = new Item();
        $item2->setFeedId(4);
        $item2->setGuidHash('yo');
        $return = [
            $createdFeed,
            [$item1, $item2]
        ];

        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, $url)
            ->will($this->throwException(new DoesNotExistException('no')));
        $this->explorer->expects($this->never())
            ->method('discover')
            ->with($url)
            ->will($this->returnValue([]));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($url)
            ->will($this->returnValue($return));


        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($createdFeed)
            ->will(
                $this->returnCallback(
                    function () use ($createdFeed) {
                        $createdFeed->setId(4);
                        return $createdFeed;
                    }
                )
            );

        $feed = $this->class->create(
            $this->uid,
            $url,
            $folderId,
            false,
            null,
            'user',
            'pass'
        );

        $this->assertEquals($feed->getFolderId(), $folderId);
        $this->assertEquals($feed->getUrl(), $url);
        $this->assertEquals($feed->getArticlesPerUpdate(), 2);
        $this->assertEquals($feed->getBasicAuthUser(), 'user');
        $this->assertEquals($feed->getBasicAuthPassword(), 'pass');
    }

    public function testCreateSetsTitle()
    {
        $url = 'http://test';
        $folderId = 10;
        $createdFeed = new Feed();
        $createdFeed->setUrl($url);
        $createdFeed->setUrlHash('hsssi');
        $createdFeed->setLink($url);
        $createdFeed->setTitle('hehoy');
        $createdFeed->setBasicAuthUser('user');
        $createdFeed->setBasicAuthPassword('pass');
        $item1 = new Item();
        $item1->setFeedId(4);
        $item1->setGuidHash('hi');
        $item2 = new Item();
        $item2->setFeedId(4);
        $item2->setGuidHash('yo');
        $return = [
            $createdFeed,
            [$item1, $item2]
        ];

        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, $url)
            ->will($this->throwException(new DoesNotExistException('no')));
        $this->explorer->expects($this->never())
                       ->method('discover')
                       ->with($url)
                       ->will($this->returnValue([]));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($url)
            ->will($this->returnValue($return));

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($createdFeed)
            ->will(
                $this->returnCallback(
                    function () use ($createdFeed) {
                        $createdFeed->setId(4);
                        return $createdFeed;
                    }
                )
            );

        $feed = $this->class->create(
            $this->uid,
            $url,
            $folderId,
            false,
            'title',
            'user',
            'pass'
        );

        $this->assertEquals($feed->getFolderId(), $folderId);
        $this->assertEquals($feed->getUrl(), $url);
        $this->assertEquals($feed->getArticlesPerUpdate(), 2);
        $this->assertEquals($feed->getTitle(), 'title');
        $this->assertEquals($feed->getBasicAuthUser(), 'user');
        $this->assertEquals($feed->getBasicAuthPassword(), 'pass');
    }

    public function testCreateDiscovers()
    {
        $url = 'http://test';
        $folderId = 10;
        $createdFeed = new Feed();
        $createdFeed->setUrl($url);
        $createdFeed->setUrlHash('hsssi');
        $createdFeed->setLink($url);
        $createdFeed->setTitle('hehoy');
        $createdFeed->setBasicAuthUser('user');
        $createdFeed->setBasicAuthPassword('pass');
        $item1 = new Item();
        $item1->setFeedId(4);
        $item1->setGuidHash('hi');
        $item2 = new Item();
        $item2->setFeedId(4);
        $item2->setGuidHash('yo');
        $return = [
            $createdFeed,
            [$item1, $item2]
        ];

        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, 'http://discover.test')
            ->will($this->throwException(new DoesNotExistException('no')));
        $this->explorer->expects($this->once())
                       ->method('discover')
                       ->with($url)
                       ->will($this->returnValue(['http://discover.test']));
        $this->fetcher->expects($this->exactly(2))
            ->method('fetch')
            ->withConsecutive(
                ['http://test'],
                ['http://discover.test']
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ReadErrorException('There is no feed')), $this->returnValue($return));

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($createdFeed)
            ->will(
                $this->returnCallback(
                    function () use ($createdFeed) {
                        $createdFeed->setId(4);
                        return $createdFeed;
                    }
                )
            );

        $feed = $this->class->create(
            $this->uid,
            $url,
            $folderId,
            false,
            null,
            'user',
            'pass'
        );

        $this->assertEquals($feed->getFolderId(), $folderId);
        $this->assertEquals($feed->getUrl(), $url);
        $this->assertEquals($feed->getArticlesPerUpdate(), 2);
        $this->assertEquals($feed->getBasicAuthUser(), 'user');
        $this->assertEquals($feed->getBasicAuthPassword(), 'pass');
    }


    public function testCreateItemGuidExistsAlready()
    {
        $url = 'http://test';
        $folderId = 10;
        $ex = new DoesNotExistException('yo');
        $createdFeed = new Feed();
        $createdFeed->setUrl($url);
        $createdFeed->setUrlHash($url);
        $createdFeed->setLink($url);
        $item1 = new Item();
        $item1->setFeedId(5);
        $item1->setGuidHash('hi');
        $item2 = new Item();
        $item2->setFeedId(5);
        $item2->setGuidHash('yo');
        $return = [
            $createdFeed,
            [$item1, $item2]
        ];

        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, $url)
            ->will($this->throwException($ex));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($url))
            ->will($this->returnValue($return));
        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($createdFeed))
            ->will(
                $this->returnCallback(
                    function () use ($createdFeed) {
                        $createdFeed->setId(5);
                        return $createdFeed;
                    }
                )
            );

        $feed = $this->class->create($this->uid, $url, $folderId);

        $this->assertEquals($feed->getFolderId(), $folderId);
        $this->assertEquals($feed->getUrl(), $url);
    }

    public function testCreateUnableToFetchFeed()
    {
        $url = 'http://test';
        $folderId = 10;

        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($url)
            ->willReturn([null, []]);

        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, $url)
            ->will($this->throwException(new DoesNotExistException('no')));

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('Failed to fetch feed');

        $this->class->create($this->uid, $url, $folderId);
    }

    public function testCreateUnableToParseFeed()
    {
        $url = 'http://test';
        $folderId = 10;

        $this->fetcher->expects($this->exactly(2))
            ->method('fetch')
            ->with($url)
            ->will($this->throwException(new ReadErrorException('ERROR')));

        $this->mapper->expects($this->never())
            ->method('findByURL')
            ->with($this->uid, $url)
            ->will($this->throwException(new DoesNotExistException('no')));

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('ERROR');

        $this->class->create($this->uid, $url, $folderId);
    }

    public function testFetchReturnsOnBlock()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $feed->expects($this->once())
             ->method('getPreventUpdate')
             ->will($this->returnValue(true));

        $this->assertSame($feed, $this->class->fetch($feed));
    }

    public function testFetchAllReturnsOnAllBlock()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->mapper->expects($this->once())
                     ->method('findAll')
                     ->will($this->returnValue([$feed, $feed]));

        $feed->expects($this->exactly(2))
             ->method('getPreventUpdate')
             ->will($this->returnValue(true));

        $this->class->fetchAll();
    }

    public function testFetchReturnsOnReadError()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $feed->expects($this->once())
             ->method('getPreventUpdate')
             ->will($this->returnValue(false));

        $feed->expects($this->once())
             ->method('getLocation')
             ->will($this->returnValue('location'));

        $this->fetcher->expects($this->once())
                      ->method('fetch')
                      ->will($this->throwException(new ReadErrorException('FAIL')));

        $feed->expects($this->once())
            ->method('getUpdateErrorCount')
            ->will($this->returnValue(1));

        $feed->expects($this->once())
            ->method('setUpdateErrorCount')
            ->with(2);

        $feed->expects($this->once())
            ->method('setLastUpdateError')
            ->with('FAIL');

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($feed);

        $this->class->fetch($feed);
    }

    public function testFetchReturnsNoUpdate()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $feed->expects($this->once())
             ->method('getPreventUpdate')
             ->will($this->returnValue(false));

        $feed->expects($this->once())
             ->method('getLocation')
             ->will($this->returnValue('location'));

        $this->fetcher->expects($this->once())
                      ->method('fetch')
                      ->will($this->returnValue([null, []]));

        $this->mapper->expects($this->never())
            ->method('update');

        $this->assertSame($feed, $this->class->fetch($feed));
    }

    public function testFetchSucceedsEmptyItems()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $feed->expects($this->once())
             ->method('getPreventUpdate')
             ->will($this->returnValue(false));

        $feed->expects($this->once())
             ->method('getLocation')
             ->will($this->returnValue('location'));

        $feed->expects($this->once())
             ->method('setUnreadCount')
             ->with(0)
             ->will($this->returnSelf());

        $new_feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetcher->expects($this->once())
                      ->method('fetch')
                      ->will($this->returnValue([$new_feed, []]));

        $this->mapper->expects($this->exactly(1))
            ->method('update')
            ->with($feed)
            ->will($this->returnValue($feed));

        $this->assertEquals($feed, $this->class->fetch($feed));
    }

    public function testFetchSucceedsFullItems()
    {
        $feed = Feed::fromParams([
            'id'         => 1,
            'location'   => 'url.com',
            'updateMode' => 1,
        ]);

        $new_feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item1 = Item::fromParams(['id' => 1, 'body' => '1']);
        $item2 = Item::fromParams(['id' => 2, 'body' => '2']);
        $this->fetcher->expects($this->once())
                      ->method('fetch')
                      ->will($this->returnValue([$new_feed, [$item1, $item2]]));

        $this->mapper->expects($this->exactly(1))
            ->method('update')
            ->with($feed)
            ->will($this->returnValue($feed));

        $this->purifier->expects($this->exactly(2))
            ->method('purify')
            ->withConsecutive(['2', null], ['1', null])
            ->will($this->returnArgument(0));

        $this->itemService->expects($this->exactly(2))
            ->method('insertOrUpdate')
            ->withConsecutive([$item2], [$item1])
            ->will($this->returnValue($feed));

        $this->assertSame($feed, $this->class->fetch($feed));
        $this->assertEquals(2, $feed->getUnreadCount());
    }

    public function testMarkDeleted()
    {
        $feed = Feed::fromParams(['id' => 3]);
        $feed2 = Feed::fromParams(['id' => 3]);
        $feed2->setDeletedAt($this->time);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->uid, 3)
            ->will($this->returnValue($feed));
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($feed);

        $this->class->update($this->uid, $feed);
    }


    public function testUnmarkDeleted()
    {
        $feed = Feed::fromParams(['id' => 3]);
        $feed2 = Feed::fromParams(['id' => 3]);
        $feed2->setDeletedAt(0);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->uid, 3)
            ->will($this->returnValue($feed));
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($feed2);

        $this->class->update($this->uid, $feed);
    }


    public function testPurgeDeleted()
    {
        $feed1 = new Feed();
        $feed1->setId(3);
        $feed2 = new Feed();
        $feed2->setId(5);
        $feeds = [$feed1, $feed2];


        $this->mapper->expects($this->exactly(1))
            ->method('purgeDeleted')
            ->with($this->uid, 1);

        $this->class->purgeDeleted($this->uid, 1);
    }


    public function testPurgeDeletedWithoutInterval()
    {
        $this->mapper->expects($this->exactly(1))
            ->method('purgeDeleted')
            ->with($this->uid, false);

        $this->class->purgeDeleted($this->uid, false);
    }


    public function testfindAllFromAllUsers()
    {
        $expected = ['hi'];
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($expected));
        $result = $this->class->findAll();
        $this->assertEquals($expected, $result);
    }


    public function testOrdering()
    {
        $feed = Feed::fromRow(['id' => 3]);
        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->uid, $feed->getId())
            ->will($this->returnValue($feed));

        $feed->setOrdering(2);
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($feed);

        $this->class->update($this->uid, $feed);
    }


    public function testPatchEnableFullText()
    {
        $feed = Feed::fromRow(
            [
                'id' => 3,
                'http_last_modified' => 1,
                'full_text_enabled' => false
            ]
        );
        $feed2 = Feed::fromRow(['id' => 3]);
        $this->mapper->expects($this->exactly(1))
            ->method('findFromUser')
            ->with($this->uid, $feed->getId())
            ->willReturnOnConsecutiveCalls($this->returnValue($feed));

        $feed2->setFullTextEnabled(false);
        $feed2->setHttpLastModified('1');
        $feed2->resetUpdatedFields();

        $this->mapper->expects($this->exactly(1))
            ->method('update')
            ->with($feed2);

        $this->class->update($this->uid, $feed);
    }

    public function testPatchDoesNotExist()
    {
        $this->expectException('OCA\News\Service\Exceptions\ServiceNotFoundException');
        $feed = Feed::fromRow(['id' => 3]);
        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException(new DoesNotExistException('')));

        $this->class->update($this->uid, $feed);
    }


    public function testSetPinned()
    {
        $feed = Feed::fromRow(['id' => 3, 'pinned' => false]);
        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->uid, $feed->getId())
            ->will($this->returnValue($feed));

        $feed->setPinned(true);
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($feed);

        $this->class->update($this->uid, $feed);
    }


    public function testExistsForUser()
    {
        $feed = Feed::fromRow(['id' => 3, 'pinned' => false]);
        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, 'url')
            ->will($this->returnValue($feed));

        $this->assertTrue($this->class->existsForUser($this->uid, 'url'));
    }

    public function testDoesNotExistsForUser()
    {
        $this->mapper->expects($this->once())
            ->method('findByURL')
            ->with($this->uid, 'url')
            ->will($this->throwException(new DoesNotExistException('no!')));

        $this->assertFalse($this->class->existsForUser($this->uid, 'url'));
    }

    public function testFindAllFromUser()
    {
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->uid, [])
            ->will($this->returnValue([]));

        $this->assertEquals([], $this->class->findAllForUser($this->uid, []));
    }

    public function testFindAllFromFolder()
    {
        $this->mapper->expects($this->once())
            ->method('findAllFromFolder')
            ->with(null)
            ->will($this->returnValue([]));

        $this->assertEquals([], $this->class->findAllFromFolder(null));
    }

    public function testFindAllFromUserRecursive()
    {
        $feed1 = new Feed();
        $feed1->setId(1);

        $feed2 = new Feed();
        $feed2->setId(2);

        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->uid)
            ->will($this->returnValue([$feed1, $feed2]));

        $this->itemService->expects($this->exactly(2))
                          ->method('findAllInFeed')
                          ->withConsecutive(['jack', 1], ['jack', 2])
                          ->willReturn(['a']);

        $feeds = $this->class->findAllForUserRecursive($this->uid);
        $this->assertEquals(['a'], $feeds[0]->items);
        $this->assertEquals(['a'], $feeds[1]->items);
    }

    public function testRead()
    {
        $feed1 = new Feed();
        $feed1->setId(1);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->uid, 1)
            ->will($this->returnValue($feed1));

        $this->mapper->expects($this->exactly(1))
                     ->method('read')
                     ->withConsecutive(['jack', 1, null]);

        $this->class->read($this->uid, 1);
    }
}
