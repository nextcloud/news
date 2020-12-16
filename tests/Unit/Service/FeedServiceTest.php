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

use FeedIo\Reader\ReadErrorException;

use OC\L10N\L10N;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Service\FeedService;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\FetcherException;
use OCP\IConfig;
use OCP\IL10N;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


class FeedServiceTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedMapper
     */
    private $feedMapper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemMapper
     */
    private $itemMapper;

    /** @var FeedService */
    private $feedService;

    /**
     * @var string
     */
    private $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Fetcher
     */
    private $fetcher;

    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $autoPurgeMinimumInterval;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\HTMLPurifier
     */
    private $purifier;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|L10N
     */
    private $l10n;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->time = 222;
        $this->autoPurgeMinimumInterval = 10;
        $timeFactory = $this->getMockBuilder(Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedMapper = $this
            ->getMockBuilder(FeedMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher = $this
            ->getMockBuilder(Fetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMapper = $this
            ->getMockBuilder(ItemMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purifier = $this
            ->getMockBuilder(\HTMLPurifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())
            ->method('getAppValue')
            ->with('news', 'autoPurgeMinimumInterval')
            ->will($this->returnValue($this->autoPurgeMinimumInterval));

        $this->feedService = new FeedService(
            $this->feedMapper,
            $this->fetcher, $this->itemMapper, $this->logger, $this->l10n,
            $timeFactory, $config, $this->purifier
        );
        $this->user = 'jack';
    }

    /**
     * @covers \OCA\News\Service\FeedService::findAll
     */
    public function testFindAll()
    {
        $this->response = [];
        $this->feedMapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->user)
            ->will($this->returnValue([]));

        $result = $this->feedService->findAllForUser($this->user);
        $this->assertEquals([], $result);
    }


    public function testCreateDoesNotFindFeed()
    {
        $ex = new ReadErrorException('hi');
        $url = 'test';
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($url)
            ->will($this->throwException($ex));
        $this->expectException(ServiceNotFoundException::class);
        $this->feedService->create($url, 1, $this->user);
    }

    public function testCreate()
    {
        $url = 'http://test';
        $folderId = 10;
        $createdFeed = new Feed();
        $ex = new DoesNotExistException('yo');
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

        $this->feedMapper->expects($this->once())
            ->method('findByUrlHash')
            ->with(
                $this->equalTo($createdFeed->getUrlHash()),
                $this->equalTo($this->user)
            )
            ->will($this->throwException($ex));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($url))
            ->will($this->returnValue($return));
        $this->feedMapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($createdFeed))
            ->will(
                $this->returnCallback(
                    function () use ($createdFeed) {
                        $createdFeed->setId(4);
                        return $createdFeed;
                    }
                )
            );
        $this->itemMapper->expects($this->exactly(2))
            ->method('findByGuidHash')
            ->withConsecutive(
                [$item2->getGuidHash(), $item2->getFeedId(), $this->user],
                [$item1->getGuidHash(), $item1->getFeedId(), $this->user]
            )
            ->will($this->throwException($ex));

        $this->purifier->expects($this->exactly(2))
            ->method('purify')
            ->withConsecutive(
                [$return[1][1]->getBody()],
                [$return[1][0]->getBody()]
            )
            ->willReturnOnConsecutiveCalls(
                $return[1][1]->getBody(),
                $return[1][0]->getBody()
            );

        $this->itemMapper->expects($this->exactly(2))
            ->method('insert')
            ->withConsecutive([$return[1][1]], [$return[1][0]]);

        $feed = $this->feedService->create(
            $url, $folderId, $this->user, null,
            'user', 'pass'
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

        $this->feedMapper->expects($this->once())
            ->method('findByUrlHash')
            ->with(
                $this->equalTo($createdFeed->getUrlHash()),
                $this->equalTo($this->user)
            )
            ->will($this->throwException($ex));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($url))
            ->will($this->returnValue($return));
        $this->feedMapper->expects($this->once())
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
        $this->itemMapper->expects($this->exactly(2))
            ->method('findByGuidHash')
            ->withConsecutive(
                [$item2->getGuidHash(), $item2->getFeedId(), $this->user],
                [$item1->getGuidHash(), $item1->getFeedId(), $this->user]
            )
            ->willReturnOnConsecutiveCalls($this->throwException($ex), null);
        $this->purifier->expects($this->exactly(1))
            ->method('purify')
            ->withConsecutive([$return[1][1]->getBody()])
            ->willReturnOnConsecutiveCalls($return[1][1]->getBody());
        $this->itemMapper->expects($this->exactly(1))
            ->method('insert')
            ->withConsecutive([$return[1][1]]);

        $feed = $this->feedService->create($url, $folderId, $this->user);

        $this->assertEquals($feed->getFolderId(), $folderId);
        $this->assertEquals($feed->getUrl(), $url);
        $this->assertEquals(1, $feed->getUnreadCount());
    }

    public function testCreateUnableToParseFeed()
    {
        $url = 'http://test';
        $folderId = 10;

        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($url))
            ->willReturn([null, []]);

        $this->l10n->expects($this->once())
            ->method('t')
            ->with($this->equalTo('Can not add feed: Unable to parse feed'))
            ->willReturn('Can not add feed: Unable to parse feed');

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('Can not add feed: Unable to parse feed');

        $this->feedService->create($url, $folderId, $this->user);
    }

    public function testUpdateCreatesNewEntry()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setArticlesPerUpdate(1);
        $feed->setLink('http://test');
        $feed->setUrl('http://test');
        $feed->setUrlHash('yo');
        $feed->setHttpLastModified(3);
        $feed->setHttpEtag(4);

        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setFeedId(3);
        $items = [$item];

        $ex = new DoesNotExistException('hi');

        $fetchReturn = [$feed, $items];

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with(
                $this->equalTo('http://test'),
                $this->equalTo(false),
                $this->equalTo(3),
                $this->equalTo(''),
                $this->equalTo('')
            )
            ->will($this->returnValue($fetchReturn));
        $this->feedMapper->expects($this->exactly(1))
            ->method('update')
            ->with($this->equalTo($feed));
        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(
                $this->equalTo($items[0]->getGuidHash()),
                $this->equalTo($items[0]->getFeedId()),
                $this->equalTo($this->user)
            )
            ->will($this->throwException($ex));
        $this->purifier->expects($this->exactly(1))
            ->method('purify')
            ->with($this->equalTo($items[0]->getBody()))
            ->will($this->returnValue($items[0]->getBody()));
        $this->itemMapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($items[0]));


        $return = $this->feedService->update($this->user, $feed->getId());

        $this->assertEquals($return, $feed);
    }

    public function testForceUpdateUpdatesEntry()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setArticlesPerUpdate(1);
        $feed->setLink('http://test');
        $feed->setUrl('http://test');
        $feed->setUrlHash('yo');
        $feed->setHttpLastModified(3);
        $feed->setHttpEtag(4);

        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setFeedId(3);
        $items = [$item];

        $ex = new DoesNotExistException('hi');

        $fetchReturn = [$feed, $items];

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with(
                $this->equalTo('http://test'),
                $this->equalTo(false),
                $this->equalTo(3),
                $this->equalTo(''),
                $this->equalTo('')
            )
            ->will($this->returnValue($fetchReturn));
        $this->feedMapper->expects($this->exactly(1))
            ->method('update')
            ->with($this->equalTo($feed));
        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(
                $this->equalTo($items[0]->getGuidHash()),
                $this->equalTo($items[0]->getFeedId()),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($items[0]));
        $this->purifier->expects($this->exactly(1))
            ->method('purify')
            ->with($this->equalTo($items[0]->getBody()))
            ->will($this->returnValue($items[0]->getBody()));
        $this->itemMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($items[0]));


        $return = $this->feedService->update($this->user, $feed->getId(), true);

        $this->assertEquals($return, $feed);
    }

    private function createUpdateFeed()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setArticlesPerUpdate(1);
        $feed->setLink('http://test');
        $feed->setUrl('http://test');
        $feed->setUrlHash('yo');
        $feed->setHttpLastModified(3);
        $feed->setHttpEtag(4);
        return $feed;
    }

    private function createUpdateItem()
    {
        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setFeedId(3);
        $item->setPubDate(2);
        $item->setUpdatedDate(2);
        $item->setTitle('hey');
        $item->setAuthor('aut');
        $item->setBody('new');
        $item->setUnread(false);
        return $item;
    }

    private function createUpdateItem2()
    {
        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setFeedId(3);
        $item->setPubDate(1);
        $item->setUpdatedDate(1);
        $item->setTitle('ho');
        $item->setAuthor('auto');
        $item->setBody('old');
        $item->setUnread(false);
        return $item;
    }

    public function testUpdateUpdatesWhenUpdatedDateIsNewer()
    {
        $feed = $this->createUpdateFeed();
        $item = $this->createUpdateItem();
        $item2 = $this->createUpdateItem2();

        $items = [$item];

        $fetchReturn = [$feed, $items];

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($fetchReturn));
        $this->feedMapper->expects($this->exactly(1))
            ->method('update');
        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->will($this->returnValue($item2));
        $this->purifier->expects($this->exactly(1))
            ->method('purify')
            ->will($this->returnValue($items[0]->getBody()));
        $this->itemMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($item2));


        $return = $this->feedService->update($this->user, $feed->getId());

        $this->assertEquals($return, $feed);
    }


    public function testUpdateSetsUnreadIfModeIsOne()
    {
        $feed = $this->createUpdateFeed();
        $feed->setUpdateMode(1);
        $item = $this->createUpdateItem();
        $item2 = $this->createUpdateItem2();
        $item3 = $this->createUpdateItem();
        $item3->setUnread(true);

        $items = [$item];

        $fetchReturn = [$feed, $items];

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($fetchReturn));
        $this->feedMapper->expects($this->exactly(1))
            ->method('update');
        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->will($this->returnValue($item2));
        $this->purifier->expects($this->exactly(1))
            ->method('purify')
            ->will($this->returnValue($items[0]->getBody()));
        $this->itemMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($item3));

        $return = $this->feedService->update($this->user, $feed->getId());

        $this->assertEquals($return, $feed);

    }

    public function testUpdateUpdatesArticlesPerFeedCount()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setUrl('http://example.com');
        $feed->setUrlHash('yo');

        $existingFeed = new Feed();
        $existingFeed->setId(3);
        $existingFeed->setUrl('http://example.com');
        $feed->setArticlesPerUpdate(2);

        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setFeedId(3);
        $items = [$item];

        $this->feedMapper->expects($this->any())
            ->method('findFromUser')
            ->will($this->returnValue($existingFeed));

        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue([$feed, $items]));

        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($existingFeed));

        $this->itemMapper->expects($this->any())
            ->method('findByGuidHash')
            ->will($this->returnValue($item));

        $this->feedService->update($this->user, $feed->getId());
    }

    public function testUpdateFails()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setUrl('http://example.com');
        $feed->setUpdateErrorCount(0);
        $feed->setLastUpdateError('');

        $expectedFeed = new Feed();
        $expectedFeed->setId(3);
        $expectedFeed->setUrl('http://example.com');
        $expectedFeed->setUpdateErrorCount(1);
        $expectedFeed->setLastUpdateError('hi');

        $ex = new ReadErrorException('hi');

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->willReturnOnConsecutiveCalls($feed, $expectedFeed);
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->throwException($ex));
        $this->logger->expects($this->any())
            ->method('debug');

        $this->feedMapper->expects($this->exactly(1))
            ->method('update')
            ->with($expectedFeed)
            ->will($this->returnValue($expectedFeed));

        $return = $this->feedService->update($this->user, $feed->getId());

        $this->assertEquals($return, $expectedFeed);
    }


    public function testUpdateDoesNotFindEntry()
    {
        $feed = new Feed();
        $feed->setId(3);

        $ex = new DoesNotExistException('');

        $this->feedMapper->expects($this->exactly(1))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->throwException($ex));

        $this->expectException(ServiceNotFoundException::class);
        $this->feedService->update($this->user, $feed->getId());
    }


    public function testUpdatePassesFullText()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setUrl('https://goo.com');
        $feed->setHttpLastModified(123);
        $feed->setFullTextEnabled(true);

        $ex = new DoesNotExistException('');

        $this->feedMapper->expects($this->exactly(1))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));

        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->with(
                $this->equalTo($feed->getUrl()),
                $this->equalTo(false),
                $this->equalTo($feed->getHttpLastModified()),
                $this->equalTo($feed->getFullTextEnabled())
            )
            ->will($this->throwException($ex));

        $this->expectException(DoesNotExistException::class);
        $this->feedService->update($this->user, $feed->getId());
    }


    public function testUpdateDoesNotFindUpdatedEntry()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setArticlesPerUpdate(1);
        $feed->setUrl('http://example.com');

        $item = new Item();
        $item->setGuidHash(md5('hi'));
        $item->setPubDate(3333);
        $item->setId(4);
        $items = [$item];

        $item2 = new Item();
        $item2->setPubDate(111);

        $fetchReturn = [$feed, $items];
        $ex = new DoesNotExistException('');

        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->willReturnOnConsecutiveCalls($feed, $this->throwException($ex));
        $this->feedMapper->expects($this->exactly(1))
            ->method('update')
            ->with($this->equalTo($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($fetchReturn));
        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(
                $this->equalTo($item->getGuidHash()),
                $this->equalTo($feed->getId()),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($item2));

        $this->expectException(ServiceNotFoundException::class);
        $this->feedService->update($this->user, $feed->getId());
    }


    public function testUpdateDoesntUpdateIfFeedIsPrevented()
    {
        $feedId = 3;
        $feed = new Feed();
        $feed->setFolderId(16);
        $feed->setId($feedId);
        $feed->setPreventUpdate(true);

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->never())
            ->method('fetch');

        $this->feedService->update($this->user, $feedId);
    }


    public function testUpdateDoesntUpdateIfNoFeed()
    {
        $feedId = 3;
        $feed = new Feed();
        $feed->setFolderId(16);
        $feed->setId($feedId);
        $feed->setUrl('http://example.com');

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));
        $this->fetcher->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue([null, null]));

        $return = $this->feedService->update($this->user, $feedId);
        $this->assertEquals($feed, $return);
    }


    public function testMove()
    {
        $feedId = 3;
        $folderId = 4;
        $feed = new Feed();
        $feed->setFolderId(16);
        $feed->setId($feedId);

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->user, $feedId)
            ->will($this->returnValue($feed));

        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed));

        $this->feedService->patch(
            $feedId, $this->user, ['folderId' => $folderId]
        );

        $this->assertEquals($folderId, $feed->getFolderId());
    }


    public function testRenameFeed()
    {
        $feedId = 3;
        $feedTitle = "New Feed Title";
        $feed = new Feed();
        $feed->setTitle("Feed Title");
        $feed->setId($feedId);

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($this->user), $this->equalTo($feedId))
            ->will($this->returnValue($feed));

        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed));

        $this->feedService->patch(
            $feedId, $this->user, ['title' => $feedTitle]
        );

        $this->assertEquals($feedTitle, $feed->getTitle());
    }


    public function testImportArticles()
    {
        $url = 'http://nextcloud/nofeed';

        $feed = new Feed();
        $feed->setId(3);
        $feed->setUserId($this->user);
        $feed->setUrl($url);
        $feed->setLink($url);
        $feed->setTitle('Articles without feed');
        $feed->setAdded($this->time);
        $feed->setFolderId(0);
        $feed->setPreventUpdate(true);

        $feeds = [$feed];

        $item = new Item();
        $item->setFeedId(3);
        $item->setAuthor('john');
        $item->setGuid('s');
        $item->setGuidHash('03c7c0ace395d80182db07ae2c30f034');
        $item->setTitle('hey');
        $item->setPubDate(333);
        $item->setBody('come over');
        $item->setEnclosureMime('mime');
        $item->setEnclosureLink('lin');
        $item->setUnread(true);
        $item->setStarred(false);
        $item->generateSearchIndex();

        $json = $item->toExport(['feed3' => $feed]);

        $items = [$json];

        $this->feedMapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));

        $this->itemMapper->expects($this->once())
            ->method('findByGuidHash')
            ->will($this->throwException(new DoesNotExistException('yo')));
        $this->itemMapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($item));

        $this->purifier->expects($this->once())
            ->method('purify')
            ->with($this->equalTo($item->getBody()))
            ->will($this->returnValue($item->getBody()));

        $result = $this->feedService->importArticles($items, $this->user);

        $this->assertEquals(null, $result);
    }


    public function testImportArticlesCreatesOwnFeedWhenNotFound()
    {
        $url = 'http://nextcloud/args';

        $feed = new Feed();
        $feed->setId(3);
        $feed->setUserId($this->user);
        $feed->setUrl($url);
        $feed->setLink($url);
        $feed->setTitle('Articles without feed');
        $feed->setAdded($this->time);
        $feed->setFolderId(0);
        $feed->setPreventUpdate(true);

        $feeds = [$feed];

        $item = new Item();
        $item->setFeedId(3);
        $item->setAuthor('john');
        $item->setGuid('s');
        $item->setGuidHash('03c7c0ace395d80182db07ae2c30f034');
        $item->setTitle('hey');
        $item->setPubDate(333);
        $item->setBody('come over');
        $item->setEnclosureMime('mime');
        $item->setEnclosureLink('lin');
        $item->setUnread(true);
        $item->setStarred(false);
        $item->generateSearchIndex();

        $json = $item->toExport(['feed3' => $feed]);
        $json2 = $json;
        // believe it or not this copies stuff :D
        $json2['feedLink'] = 'http://test.com';

        $items = [$json, $json2];

        $insertFeed = new Feed();
        $insertFeed->setLink('http://nextcloud/nofeed');
        $insertFeed->setUrl('http://nextcloud/nofeed');
        $insertFeed->setUserId($this->user);
        $insertFeed->setTitle('Articles without feed');
        $insertFeed->setAdded($this->time);
        $insertFeed->setPreventUpdate(true);
        $insertFeed->setFolderId(null);

        $this->l10n->expects($this->once())
            ->method('t')
            ->will($this->returnValue('Articles without feed'));
        $this->feedMapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->feedMapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($insertFeed))
            ->will(
                $this->returnCallback(
                    function () use ($insertFeed) {
                        $insertFeed->setId(3);
                        return $insertFeed;
                    }
                )
            );


        $this->itemMapper->expects($this->exactly(2))
            ->method('findByGuidHash')
            ->withConsecutive(['03c7c0ace395d80182db07ae2c30f034', 3, $this->user], ['03c7c0ace395d80182db07ae2c30f034', 3, $this->user])
            ->willReturnOnConsecutiveCalls($this->throwException(new DoesNotExistException('yo')), $item);
        $this->purifier->expects($this->once())
            ->method('purify')
            ->with($this->equalTo($item->getBody()))
            ->will($this->returnValue($item->getBody()));
        $this->itemMapper->expects($this->exactly(1))
            ->method('insert')
            ->with($this->equalTo($item));
        $this->itemMapper->expects($this->exactly(1))
            ->method('update')
            ->with($this->equalTo($item));

        $this->feedMapper->expects($this->once())
            ->method('findByUrlHash')
            ->will($this->returnValue($feed));

        $result = $this->feedService->importArticles($items, $this->user);

        $this->assertEquals($feed, $result);
    }


    public function testMarkDeleted()
    {
        $id = 3;
        $feed = new Feed();
        $feed2 = new Feed();
        $feed2->setDeletedAt($this->time);

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($this->user), $this->equalTo($id))
            ->will($this->returnValue($feed));
        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed2));

        $this->feedService->markDeleted($id, $this->user);
    }


    public function testUnmarkDeleted()
    {
        $id = 3;
        $feed = new Feed();
        $feed2 = new Feed();
        $feed2->setDeletedAt(0);

        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($this->user), $this->equalTo($id))
            ->will($this->returnValue($feed));
        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed2));

        $this->feedService->unmarkDeleted($id, $this->user);
    }


    public function testPurgeDeleted()
    {
        $feed1 = new Feed();
        $feed1->setId(3);
        $feed2 = new Feed();
        $feed2->setId(5);
        $feeds = [$feed1, $feed2];

        $time = $this->time - $this->autoPurgeMinimumInterval;
        $this->feedMapper->expects($this->once())
            ->method('getToDelete')
            ->with($this->equalTo($time), $this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->feedMapper->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive([$feed1], [$feed2]);

        $this->feedService->purgeDeleted($this->user);
    }


    public function testPurgeDeletedWithoutInterval()
    {
        $feed1 = new Feed();
        $feed1->setId(3);
        $feed2 = new Feed();
        $feed2->setId(5);
        $feeds = [$feed1, $feed2];

        $this->feedMapper->expects($this->once())
            ->method('getToDelete')
            ->with($this->equalTo(null), $this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->feedMapper->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive([$feed1], [$feed2]);

        $this->feedService->purgeDeleted($this->user, false);
    }


    public function testfindAllFromAllUsers()
    {
        $expected = ['hi'];
        $this->feedMapper->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($expected));
        $result = $this->feedService->findAllFromAllUsers();
        $this->assertEquals($expected, $result);
    }


    public function testDeleteUser()
    {
        $this->feedMapper->expects($this->once())
            ->method('deleteUser')
            ->will($this->returnValue($this->user));

        $this->feedService->deleteUser($this->user);
    }


    public function testOrdering()
    {
        $feed = Feed::fromRow(['id' => 3]);
        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($feed->getId())
            )
            ->will($this->returnValue($feed));

        $feed->setOrdering(2);
        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed));

        $this->feedService->patch(3, $this->user, ['ordering' => 2]);
    }


    public function testPatchEnableFullText()
    {
        $feed = Feed::fromRow(
            [
                'id' => 3,
                'http_etag' => 'a',
                'http_last_modified' => 1,
                'full_text_enabled' => false
            ]
        );
        $feed2 = Feed::fromRow(['id' => 3]);
        $this->feedMapper->expects($this->exactly(2))
            ->method('findFromUser')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($feed->getId())
            )
            ->willReturnOnConsecutiveCalls($this->returnValue($feed), $this->throwException(new DoesNotExistException('')));

        $feed2->setFullTextEnabled(true);
        $feed2->setHttpEtag('');
        $feed2->setHttpLastModified(0);
        $this->feedMapper->expects($this->exactly(1))
            ->method('update')
            ->with($this->equalTo($feed2));

        $this->expectException(ServiceNotFoundException::class);

        $this->feedService->patch(3, $this->user, ['fullTextEnabled' => true]);
    }

    public function testPatchDoesNotExist()
    {
        $this->expectException('OCA\News\Service\Exceptions\ServiceNotFoundException');
        $feed = Feed::fromRow(['id' => 3]);
        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException(new DoesNotExistException('')));

        $this->feedService->patch(3, $this->user);
    }


    public function testSetPinned()
    {
        $feed = Feed::fromRow(['id' => 3, 'pinned' => false]);
        $this->feedMapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->user, $feed->getId())
            ->will($this->returnValue($feed));

        $feed->setPinned(true);
        $this->feedMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feed));

        $this->feedService->patch(3, $this->user, ['pinned' => true]);
    }


}
