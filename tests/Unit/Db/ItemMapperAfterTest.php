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

namespace OCA\News\Tests\Unit\Db;

use OC\DB\QueryBuilder\Literal;
use OCA\News\Db\Feed;
use OCA\News\Db\FeedMapperV2;
use OCA\News\Db\Folder;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Db\NewsMapperV2;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\IResult;
use OCP\DB\QueryBuilder\IExpressionBuilder;
use OCP\DB\QueryBuilder\IFunctionBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\QueryBuilder\IQueryFunction;
use OCP\IDBConnection;
use Test\TestCase;

/**
 * Class ItemMapperTest
 *
 * @package OCA\News\Tests\Unit\Db
 */
class ItemMapperAfterTest extends MapperTestUtility
{

    /** @var Time */
    private $time;
    /** @var ItemMapperV2 */
    private $class;

    /**
     * @covers \OCA\News\Db\ItemMapperV2::__construct
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->time = $this->getMockBuilder(Time::class)
                           ->getMock();

        $this->class = new ItemMapperV2($this->db, $this->time);
    }

    public function testFindAllInFeedAfter()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['feeds.id = :feedId']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'feedId' => 4,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllInFeedAfter('jack', 4, 1610903351, false);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllInFeedAfterHideRead()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(4))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['feeds.id = :feedId'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'feedId' => 4,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllInFeedAfter('jack', 4, 1610903351, true);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllInFolderAfter()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('innerJoin')
            ->withConsecutive(
                ['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'],
                ['feeds', 'news_folders', 'folders', 'feeds.folder_id = folders.id']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['folders.id = :folderId']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'folderId' => 4,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllInFolderAfter('jack', 4, 1610903351, false);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllInFolderAfterHideRead()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('innerJoin')
            ->withConsecutive(
                ['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'],
                ['feeds', 'news_folders', 'folders', 'feeds.folder_id = folders.id']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(4))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['folders.id = :folderId'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'folderId' => 4,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllInFolderAfter('jack', 4, 1610903351, true);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllAfterUnread()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('innerJoin')
            ->withConsecutive(['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllAfter('jack', 6, 1610903351);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllAfterStarred()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('innerJoin')
            ->withConsecutive(['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId'],
                ['items.starred = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllAfter('jack', 2, 1610903351);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllAfterAll()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('innerJoin')
            ->withConsecutive(['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllAfter('jack', 3, 1610903351);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllAfterInvalid()
    {
        $this->expectException(ServiceValidationException::class);
        $this->expectExceptionMessage('Unexpected Feed type in call');

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('innerJoin')
            ->withConsecutive(['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                ['items.last_modified >= :updatedSince'],
                ['feeds.user_id = :userId']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameters')
            ->with([
                'updatedSince' => 1610903351,
                'userId' => 'jack',
            ])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->never())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->never())
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllAfter('jack', 232, 1610903351);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

}