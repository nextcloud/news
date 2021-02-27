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
class ItemMapperPaginatedTest extends MapperTestUtility
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

    public function testFindAllItemsInvalid()
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
                ['feeds.user_id = :userId']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
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

        $this->class->findAllItems('jack', 232, 10, 10, false, []);
    }

    public function testFindAllItemsFullInverted()
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
                ['feeds.user_id = :userId'],
                ['items.id > :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'ASC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'ASC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllItems('jack', 3, 10, 10, true, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllItemsUnread()
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
                ['feeds.user_id = :userId'],
                ['items.id < :offset'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllItems('jack', 6, 10, 10, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllItemsStarred()
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
                ['feeds.user_id = :userId'],
                ['items.id < :offset'],
                ['items.starred = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllItems('jack', 2, 10, 10, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllItemsStarredSearch()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);
        $this->db->expects($this->exactly(2))
            ->method('escapeLikeParameter')
            ->will($this->returnArgument(0));

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

        $this->builder->expects($this->exactly(5))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['items.search_index LIKE :term0'],
                ['items.search_index LIKE :term1'],
                ['items.id < :offset'],
                ['items.starred = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(4))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['term0', '%key%'], ['term1', '%word%'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllItems('jack', 2, 10, 10, false, ['key', 'word']);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFeed()
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
                ['feeds.user_id = :userId'],
                ['items.feed_id = :feedId'],
                ['items.id < :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['feedId', 2], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFeed('jack', 2, 10, 10, false, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFeedInverted()
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
                ['feeds.user_id = :userId'],
                ['items.feed_id = :feedId'],
                ['items.id > :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['feedId', 2], ['offset', 10])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'ASC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'ASC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFeed('jack', 2, 10, 10, false, true, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFeedHideRead()
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

        $this->builder->expects($this->exactly(4))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['items.feed_id = :feedId'],
                ['items.id < :offset'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['feedId', 2], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFeed('jack', 2, 10, 10, true, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFeedSearch()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);
        $this->db->expects($this->exactly(2))
            ->method('escapeLikeParameter')
            ->will($this->returnArgument(0));

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

        $this->builder->expects($this->exactly(5))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['items.feed_id = :feedId'],
                ['items.search_index LIKE :term0'],
                ['items.search_index LIKE :term1'],
                ['items.id < :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(5))
            ->method('setParameter')
            ->withConsecutive(
                ['userId', 'jack'],
                ['feedId', 2],
                ['term0', '%key%'],
                ['term1', '%word%'],
                ['offset', 10]
            )
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFeed('jack', 2, 10, 10, false, false, ['key', 'word']);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFolderIdNull()
    {
        $expr = $this->getMockBuilder(IExpressionBuilder::class)
                     ->getMock();

        $expr->expects($this->once())
             ->method('isNull')
             ->with('feeds.folder_id')
             ->will($this->returnValue('x IS NULL'));

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->exactly(1))
            ->method('expr')
            ->will($this->returnValue($expr));

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
                ['feeds.user_id = :userId'],
                ['x IS NULL'],
                ['items.id < :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFolder('jack', null, 10, 10, false, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFolderHideRead()
    {
        $expr = $this->getMockBuilder(IExpressionBuilder::class)
                     ->getMock();

        $expr->expects($this->once())
             ->method('isNull')
             ->with('feeds.folder_id')
             ->will($this->returnValue('x IS NULL'));

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->exactly(1))
            ->method('expr')
            ->will($this->returnValue($expr));

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

        $this->builder->expects($this->exactly(4))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['x IS NULL'],
                ['items.id < :offset'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFolder('jack', null, 10, 10, true, false, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFolderHideReadInvertOrder()
    {
        $expr = $this->getMockBuilder(IExpressionBuilder::class)
                     ->getMock();

        $expr->expects($this->once())
             ->method('isNull')
             ->with('feeds.folder_id')
             ->will($this->returnValue('x IS NULL'));

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->exactly(1))
            ->method('expr')
            ->will($this->returnValue($expr));

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

        $this->builder->expects($this->exactly(4))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['x IS NULL'],
                ['items.id > :offset'],
                ['items.unread = 1']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'ASC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'ASC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFolder('jack', null, 10, 10, true, true, []);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

    public function testFindAllFolderSearchId()
    {
        $expr = $this->getMockBuilder(IExpressionBuilder::class)
                     ->getMock();

        $this->builder->expects($this->exactly(1))
            ->method('expr')
            ->will($this->returnValue($expr));

        $expr->expects($this->once())
             ->method('eq')
             ->with('feeds.folder_id', new Literal(2))
             ->will($this->returnValue('x = y'));

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);
        $this->db->expects($this->exactly(2))
            ->method('escapeLikeParameter')
            ->will($this->returnArgument(0));

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

        $this->builder->expects($this->exactly(5))
            ->method('andWhere')
            ->withConsecutive(
                ['feeds.user_id = :userId'],
                ['x = y'],
                ['items.search_index LIKE :term0'],
                ['items.search_index LIKE :term1'],
                ['items.id < :offset']
            )
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(4))
            ->method('setParameter')
            ->withConsecutive(['userId', 'jack'], ['term0', '%key%'], ['term1', '%word%'], ['offset', 10])
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(10)
            ->will($this->returnSelf());


        $this->builder->expects($this->exactly(0))
            ->method('setFirstResult')
            ->with(10)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findAllFolder('jack', 2, 10, 10, false, false, ['key', 'word']);
        $this->assertEquals([Item::fromRow(['id' => 4])], $result);
    }

}