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

use OC\Log;
use OCA\News\Db\ItemMapper;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\ItemService;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Utility\PsrLogger;
use OCA\News\Utility\Time;
use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Item;
use \OCA\News\Db\FeedType;
use OCP\IConfig;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


class ItemServiceTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemMapper
     */
    private $oldItemMapper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemMapperV2
     */
    private $mapper;
    /**
     * @var  ItemService
     */
    private $itemService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IConfig
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Time
     */
    private $timeFactory;

    /**
     * @var int
     */
    private $newestItemId;

    /**
     * @var string
     */
    private $time;


    protected function setUp(): void
    {
        $this->time = '222';
        $this->timeFactory = $this->getMockBuilder(Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->timeFactory->expects($this->any())
            ->method('getMicroTime')
            ->will($this->returnValue($this->time));
        $this->mapper = $this->getMockBuilder(ItemMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->oldItemMapper = $this->getMockBuilder(ItemMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemService = new ItemService(
            $this->mapper,
            $this->oldItemMapper,
            $this->timeFactory,
            $this->config,
            $this->logger
        );
        $this->user = 'jack';
        $this->id = 3;
        $this->updatedSince = 20333;
        $this->showAll = true;
        $this->offset = 5;
        $this->limit = 20;
        $this->newestItemId = 4;
    }


    public function testFindAllNewFeed()
    {
        $type = FeedType::FEED;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllNewFeed')
            ->with(
                $this->equalTo(3),
                $this->equalTo(20333),
                $this->equalTo(true),
                $this->equalTo('jack')
            )
            ->will($this->returnValue([]));

        $result = $this->itemService->findAllNew(3, $type, 20333, true, 'jack');
        $this->assertEquals([], $result);
    }


    public function testFindAllNewFolder()
    {
        $type = FeedType::FOLDER;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllNewFolder')
            ->with(
                $this->equalTo(3),
                $this->equalTo(20333),
                $this->equalTo(true),
                $this->equalTo('jack')
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllNew(3, $type, 20333, true, 'jack');
        $this->assertEquals(['val'], $result);
    }


    public function testFindAllNew()
    {
        $type = FeedType::STARRED;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllNew')
            ->with(
                $this->equalTo(20333),
                $this->equalTo($type),
                $this->equalTo(true),
                $this->equalTo('jack')
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllNew(
            3, $type, 20333, true,
            'jack'
        );
        $this->assertEquals(['val'], $result);
    }


    public function testFindAllFeed()
    {
        $type = FeedType::FEED;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllFeed')
            ->with(
                $this->equalTo(3),
                $this->equalTo(20),
                $this->equalTo(5),
                $this->equalTo(true),
                $this->equalTo(false),
                $this->equalTo('jack'),
                $this->equalTo([])
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllItems(
            3, $type, 20, 5,
            true, false, 'jack'
        );
        $this->assertEquals(['val'], $result);
    }


    public function testFindAllFolder()
    {
        $type = FeedType::FOLDER;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllFolder')
            ->with(
                $this->equalTo(3),
                $this->equalTo(20),
                $this->equalTo(5),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo('jack'),
                $this->equalTo([])
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllItems(
            3, $type, 20, 5,
            true, true, 'jack'
        );
        $this->assertEquals(['val'], $result);
    }


    public function testFindAll()
    {
        $type = FeedType::STARRED;
        $this->oldItemMapper->expects($this->once())
            ->method('findAllItems')
            ->with(
                $this->equalTo(20),
                $this->equalTo(5),
                $this->equalTo($type),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo('jack'),
                $this->equalTo([])
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllItems(
            3, $type, 20, 5,
            true, true, 'jack'
        );
        $this->assertEquals(['val'], $result);
    }


    public function testFindAllSearch()
    {
        $type = FeedType::STARRED;
        $search = ['test'];

        $this->oldItemMapper->expects($this->once())
            ->method('findAllItems')
            ->with(
                $this->equalTo(20),
                $this->equalTo(5),
                $this->equalTo($type),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo('jack'),
                $this->equalTo($search)
            )
            ->will($this->returnValue(['val']));

        $result = $this->itemService->findAllItems(
            3, $type, 20, 5,
            true, true, 'jack', $search
        );
        $this->assertEquals(['val'], $result);
    }



    public function testStar()
    {
        $itemId = 3;
        $feedId = 5;
        $guidHash = md5('hihi');

        $item = new Item();
        $item->setId($itemId);
        $item->setStarred(false);

        $expectedItem = new Item();
        $expectedItem->setStarred(true);
        $expectedItem->setId($itemId);

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with($feedId, $guidHash)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($expectedItem));

        $this->itemService->star($feedId, $guidHash, true, 'jack');

        $this->assertTrue($item->isStarred());
    }


    public function testUnstar()
    {
        $itemId = 3;
        $feedId = 5;
        $guidHash = md5('hihi');

        $item = new Item();
        $item->setId($itemId);
        $item->setStarred(true);

        $expectedItem = new Item();
        $expectedItem->setStarred(true); //workaround to set starred as updated field
        $expectedItem->setStarred(false);
        $expectedItem->setId($itemId);

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with($feedId, $guidHash)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($expectedItem));

        $this->itemService->star($feedId, $guidHash, false, 'jack');

        $this->assertFalse($item->isStarred());
    }

    public function testRead()
    {
        $itemId = 3;
        $item = new Item();
        $item->setId($itemId);
        $item->setUnread(true);

        $expectedItem = new Item();
        $expectedItem->setUnread(false);
        $expectedItem->setId($itemId);
        $expectedItem->setLastModified($this->time);

        $this->oldItemMapper->expects($this->once())
            ->method('readItem')
            ->with(
                $this->equalTo($itemId),
                $this->equalTo(true),
                $this->equalTo($this->time),
                $this->equalTo('jack')
            )
            ->will($this->returnValue($item));

        $this->itemService->read($itemId, true, 'jack');
    }


    public function testReadDoesNotExist()
    {

        $this->expectException(ServiceNotFoundException::class);
        $this->oldItemMapper->expects($this->once())
            ->method('readItem')
            ->will($this->throwException(new DoesNotExistException('')));

        $this->itemService->read(1, true, 'jack');
    }

    public function testStarDoesNotExist()
    {

        $this->expectException(ServiceNotFoundException::class);
        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->will($this->throwException(new DoesNotExistException('')));

        $this->itemService->star(1, 'hash', true, 'jack');
    }


    public function testReadAll()
    {
        $highestItemId = 6;

        $this->oldItemMapper->expects($this->once())
            ->method('readAll')
            ->with(
                $this->equalTo($highestItemId),
                $this->equalTo($this->time),
                $this->equalTo('jack')
            );

        $this->itemService->readAll($highestItemId, 'jack');
    }


    public function testReadFolder()
    {
        $folderId = 3;
        $highestItemId = 6;

        $this->oldItemMapper->expects($this->once())
            ->method('readFolder')
            ->with(
                $this->equalTo($folderId),
                $this->equalTo($highestItemId),
                $this->equalTo($this->time),
                $this->equalTo('jack')
            );

        $this->itemService->readFolder($folderId, $highestItemId, 'jack');
    }


    public function testReadFeed()
    {
        $feedId = 3;
        $highestItemId = 6;

        $this->oldItemMapper->expects($this->once())
            ->method('readFeed')
            ->with(
                $this->equalTo($feedId),
                $this->equalTo($highestItemId),
                $this->equalTo($this->time),
                $this->equalTo('jack')
            );

        $this->itemService->readFeed($feedId, $highestItemId, 'jack');
    }


    public function testAutoPurgeOldWillPurgeOld()
    {
        $this->config->expects($this->once())
            ->method('getAppValue')
            ->with('news', 'autoPurgeCount')
            ->will($this->returnValue(2));
        $this->oldItemMapper->expects($this->once())
            ->method('deleteReadOlderThanThreshold')
            ->with($this->equalTo(2));

        $this->itemService->autoPurgeOld();
    }

    public function testAutoPurgeOldWontPurgeOld()
    {
        $this->config->expects($this->once())
            ->method('getAppValue')
            ->with('news', 'autoPurgeCount')
            ->will($this->returnValue(-1));
        $this->oldItemMapper->expects($this->never())
            ->method('deleteReadOlderThanThreshold');

        $this->itemService->autoPurgeOld();
    }


    public function testGetNewestItemId()
    {
        $this->oldItemMapper->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo('jack'))
            ->will($this->returnValue(12));

        $result = $this->itemService->getNewestItemId('jack');
        $this->assertEquals(12, $result);
    }


    public function testGetNewestItemIdDoesNotExist()
    {
        $this->oldItemMapper->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo('jack'))
            ->will(
                $this->throwException(
                    new DoesNotExistException('There are no items')
                )
            );

        $this->expectException(ServiceNotFoundException::class);
        $this->itemService->getNewestItemId('jack');
    }


    public function testStarredCount()
    {
        $star = 18;

        $this->oldItemMapper->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo('jack'))
            ->will($this->returnValue($star));

        $result = $this->itemService->starredCount('jack');

        $this->assertEquals($star, $result);
    }


    public function testGetUnreadOrStarred()
    {
        $this->oldItemMapper->expects($this->once())
            ->method('findAllUnreadOrStarred')
            ->with($this->equalTo('jack'))
            ->will($this->returnValue([]));

        $result = $this->itemService->getUnreadOrStarred('jack');

        $this->assertEquals([], $result);
    }



}
