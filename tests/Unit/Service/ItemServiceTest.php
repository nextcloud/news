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

use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\ItemServiceV2;
use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Item;
use \OCA\News\Db\ListType;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IAppConfig;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class ItemServiceTest
 *
 * @package OCA\News\Tests\Unit\Service
 */
class ItemServiceTest extends TestCase
{

    /**
     * @var MockObject|ItemMapperV2
     */
    private $mapper;
    /**
     * @var  ItemServiceV2
     */
    private $class;

    /**
     * @var MockObject|IAppConfig
     */
    private $config;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;
    /**
     * @var int
     */
    private $newestItemId;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $offset;
    private $limit;
    private $showAll;
    private $updatedSince;
    private $id;


    protected function setUp(): void
    {
        $this->mapper = $this->getMockBuilder(ItemMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(IAppConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->class = new ItemServiceV2(
            $this->mapper,
            $this->logger,
            $this->config
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
        $this->mapper->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('jack', 2, 20333, true)
            ->will($this->returnValue([]));

        $result = $this->class->findAllInFeedAfter($this->user, 2, 20333, true);
        $this->assertEquals([], $result);
    }

    public function testFindAllNewFolder()
    {
        $this->mapper->expects($this->once())
            ->method('findAllInFolderAfter')
            ->with('jack', 2, 20333, true)
            ->will($this->returnValue([]));

        $result = $this->class->findAllInFolderAfter($this->user, 2, 20333, true);
        $this->assertEquals([], $result);
    }

    public function testFindAllNewItem()
    {
        $this->mapper->expects($this->once())
            ->method('findAllAfter')
            ->with('jack', 2, 20333)
            ->will($this->returnValue([]));

        $result = $this->class->findAllAfter($this->user, 2, 20333);
        $this->assertEquals([], $result);
    }

    public function testFindAllNewItemWrongType()
    {
        $this->expectException(ServiceValidationException::class);
        $this->expectExceptionMessage('Trying to find in unknown type');

        $this->mapper->expects($this->never())
            ->method('findAllAfter');

        $result = $this->class->findAllAfter($this->user, 5, 20333);
        $this->assertEquals([], $result);
    }

    public function testFindAllFeed()
    {
        $this->mapper->expects($this->once())
            ->method('findAllFeed')
            ->with('jack', 3, 20, 5, true, false, [])
            ->will($this->returnValue(['val']));

        $result = $this->class->findAllInFeedWithFilters(
            'jack',
            3,
            20,
            5,
            true,
            false
        );
        $this->assertEquals(['val'], $result);
    }

    public function testFindAllFolder()
    {
        $this->mapper->expects($this->once())
            ->method('findAllFolder')
            ->with('jack', 3, 20, 5, true, true, [])
            ->will($this->returnValue(['val']));

        $result = $this->class->findAllInFolderWithFilters(
            'jack',
            3,
            20,
            5,
            true,
            true,
            []
        );
        $this->assertEquals(['val'], $result);
    }

    public function testFindAllItems()
    {
        $type = ListType::STARRED;
        $this->mapper->expects($this->once())
            ->method('findAllItems')
            ->with('jack', $type, 20, 5, true, [])
            ->will($this->returnValue(['val']));

        $result = $this->class->findAllWithFilters('jack', $type, 20, 5, true);
        $this->assertEquals(['val'], $result);
    }

    public function testFindAllSearch()
    {
        $type = ListType::STARRED;
        $search = ['test'];


        $this->mapper->expects($this->once())
            ->method('findAllItems')
            ->with('jack', $type, 20, 5, true, $search)
            ->will($this->returnValue(['val']));

        $result = $this->class->findAllWithFilters('jack', $type, 20, 5, true, $search);
        $this->assertEquals(['val'], $result);
    }

    public function testFindAll()
    {
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(['val']));

        $result = $this->class->findAll();
        $this->assertEquals(['val'], $result);
    }

    public function testStarByGuid()
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
            ->method('findForUserByGuidHash')
            ->with('jack', $feedId, $guidHash)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($expectedItem));

        $this->class->starByGuid('jack', $feedId, $guidHash, true);

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
            ->method('findForUserByGuidHash')
            ->with('jack', $feedId, $guidHash)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($expectedItem));

        $this->class->starByGuid('jack', $feedId, $guidHash, false);

        $this->assertFalse($item->isStarred());
    }

    public function testRead()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $item->expects($this->once())
             ->method('setUnread')
             ->with(false);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($item)
            ->will($this->returnValue($item));

        $this->class->read('jack', 3, true);
    }

    public function testStar()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $item->expects($this->once())
             ->method('setStarred')
             ->with(true);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($item)
            ->will($this->returnValue($item));

        $this->class->star('jack', 3, true);
    }

    public function testStarByGuidDoesNotExist()
    {

        $this->expectException(ServiceNotFoundException::class);
        $this->mapper->expects($this->once())
            ->method('findForUserByGuidHash')
            ->will($this->throwException(new DoesNotExistException('')));

        $this->class->starByGuid('jack', 1, 'hash', true);
    }

    public function testStarByGuidDuplicate()
    {

        $this->expectException(ServiceConflictException::class);
        $this->mapper->expects($this->once())
            ->method('findForUserByGuidHash')
            ->will($this->throwException(new MultipleObjectsReturnedException('')));

        $this->class->starByGuid('jack', 1, 'hash', true);
    }

    public function testReadAll()
    {
        $highestItemId = 6;

        $this->mapper->expects($this->once())
            ->method('readAll')
            ->with('jack', $highestItemId);

        $this->class->readAll('jack', $highestItemId);
    }

    public function testGetNewestItemId()
    {
        $this->mapper->expects($this->once())
            ->method('newest')
            ->with($this->equalTo('jack'))
            ->will($this->returnValue(Item::fromParams(['id' => 12])));

        $result = $this->class->newest('jack');
        $this->assertEquals(12, $result->getId());
    }

    public function testGetNewestItemIdDoesNotExist()
    {
        $this->mapper->expects($this->once())
            ->method('newest')
            ->with($this->equalTo('jack'))
            ->will(
                $this->throwException(
                    new DoesNotExistException('There are no items')
                )
            );

        $this->expectException(ServiceNotFoundException::class);
        $this->class->newest('jack');
    }

    public function testGetNewestItemDuplicate()
    {
        $this->mapper->expects($this->once())
            ->method('newest')
            ->with($this->equalTo('jack'))
            ->will(
                $this->throwException(
                    new MultipleObjectsReturnedException('There are no items')
                )
            );

        $this->expectException(ServiceConflictException::class);
        $this->class->newest('jack');
    }

    public function testStarredCount()
    {
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with('jack', ['starred' => 1])
            ->will($this->returnValue([new Item(), new Item()]));

        $result = $this->class->starred('jack');

        $this->assertEquals(2, count($result));
    }

    public function testInsertOrUpdateInserts()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $item->expects($this->once())
             ->method('getFeedId')
             ->will($this->returnValue(1));

        $item->expects($this->once())
             ->method('getGuidHash')
             ->will($this->returnValue('hash'));

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(1, 'hash')
            ->will($this->throwException(new DoesNotExistException('exception')));

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($item)
            ->will($this->returnValue($item));

        $result = $this->class->insertOrUpdate($item);

        $this->assertEquals($item, $result);
    }

    public function testInsertOrUpdateUpdates()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();
        $db_item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $item->expects($this->once())
             ->method('getFeedId')
             ->will($this->returnValue(1));

        $item->expects($this->once())
             ->method('getGuidHash')
             ->will($this->returnValue('hash'));

        $item->expects($this->once())
             ->method('setUnread')
             ->with(true)
             ->will($this->returnSelf());

        $db_item->expects($this->once())
                ->method('isUnread')
                ->will($this->returnValue(true));

        $item->expects($this->once())
             ->method('setStarred')
             ->with(true)
            ->will($this->returnSelf());

        $db_item->expects($this->once())
                ->method('isStarred')
                ->will($this->returnValue(true));

        $item->expects($this->once())
            ->method('generateSearchIndex')
            ->will($this->returnSelf());

        $item->expects($this->once())
            ->method('getFingerprint')
            ->will($this->returnValue('fingerA'));

        $db_item->expects($this->once())
            ->method('getFingerprint')
            ->will($this->returnValue('fingerB'));

        $item->expects($this->never())
            ->method('resetUpdatedFields');

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(1, 'hash')
            ->will($this->returnValue($db_item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($item)
            ->will($this->returnValue($item));

        $result = $this->class->insertOrUpdate($item);

        $this->assertEquals($item, $result);
    }

    public function testInsertOrUpdateSkipsSame()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();
        $db_item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $item->expects($this->once())
             ->method('getFeedId')
             ->will($this->returnValue(1));

        $item->expects($this->once())
             ->method('getGuidHash')
             ->will($this->returnValue('hash'));

        $item->expects($this->once())
             ->method('setUnread')
             ->with(true)
             ->will($this->returnSelf());

        $db_item->expects($this->once())
                ->method('isUnread')
                ->will($this->returnValue(true));

        $item->expects($this->once())
             ->method('setStarred')
             ->with(true)
            ->will($this->returnSelf());

        $db_item->expects($this->once())
                ->method('isStarred')
                ->will($this->returnValue(true));

        $item->expects($this->once())
            ->method('generateSearchIndex')
            ->will($this->returnSelf());

        $item->expects($this->once())
            ->method('getFingerprint')
            ->will($this->returnValue('fingerA'));

        $db_item->expects($this->once())
            ->method('getFingerprint')
            ->will($this->returnValue('fingerA'));

        $item->expects($this->once())
            ->method('resetUpdatedFields');

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(1, 'hash')
            ->will($this->returnValue($db_item));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($item)
            ->will($this->returnValue($item));

        $result = $this->class->insertOrUpdate($item);

        $this->assertEquals($item, $result);
    }

    public function testFindByGuidHash()
    {
        $item = $this->getMockBuilder(Item::class)
                     ->getMock();

        $this->mapper->expects($this->once())
            ->method('findByGuidHash')
            ->with(1, 'a')
            ->will($this->returnValue($item));

        $result = $this->class->findByGuidHash(1, 'a');

        $this->assertEquals($item, $result);
    }

    public function testFindAllInFeed()
    {
        $items = [new Item(), new Item()];

        $this->mapper->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('jack', 1, PHP_INT_MIN, false)
            ->will($this->returnValue($items));

        $result = $this->class->findAllInFeed('jack', 1);

        $this->assertEquals($items, $result);
    }

    public function testPurgeOverThreshold()
    {
        $this->mapper->expects($this->once())
            ->method('deleteOverThreshold')
            ->with(1, true)
            ->will($this->returnValue(1));

        $result = $this->class->purgeOverThreshold(1, true);

        $this->assertEquals(1, $result);
    }

    public function testPurgeOverThresholdWithNegative()
    {
        $this->mapper->expects($this->never())
            ->method('deleteOverThreshold');

        $result = $this->class->purgeOverThreshold(-1, true);

        $this->assertEquals(null, $result);
    }

    public function testPurgeOverThresholdNull()
    {
        $this->config->expects($this->exactly(1))
                     ->method('getValueInt')
                     ->with('news', 'autoPurgeCount', 200)
                     ->willReturn(200);

        $this->config->expects($this->exactly(1))
                     ->method('getValueBool')
                     ->with('news', 'purgeUnread', false)
                     ->willReturn(false);

        $this->mapper->expects($this->once())
             ->method('deleteOverThreshold')
             ->with(200, false);

        $this->class->purgeOverThreshold();
    }

    public function testPurgeOverThresholdSet()
    {
        $this->config->expects($this->exactly(1))
                     ->method('getValueBool')
                     ->with('news', 'purgeUnread', false)
                     ->willReturn(false);

        $this->mapper->expects($this->once())
             ->method('deleteOverThreshold')
             ->with(5);

        $this->class->purgeOverThreshold(5);
    }
}
