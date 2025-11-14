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
use OC\DB\QueryBuilder\Parameter;
use OC\DB\ResultAdapter;
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
class ItemMapperTest extends MapperTestUtility
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

    /**
     * @covers \OCA\News\Db\ItemMapperV2::__construct
     */
    public function testSetUpSuccess(): void
    {
        $this->assertEquals('news_items', $this->class->getTableName());
    }

    /**
     * @covers \OCA\News\Db\ItemMapperV2::findAllFromUser
     */
    public function testFindAllFromUser()
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

        $this->builder->expects($this->once())
            ->method('where')
            ->with('feeds.user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('andWhere')
            ->with('feeds.deleted_at = 0')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameter')
            ->with('user_id', 'jack')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                ['id' => 5],
                null
            );

        $result = $this->class->findAllFromUser('jack', []);
        $this->assertEquals([Item::fromRow(['id' => 4]), Item::fromRow(['id' => 5])], $result);
    }

    /**
     * @covers \OCA\News\Db\ItemMapperV2::findAllFromUser
     */
    public function testFindAllFromUserWithParams()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('createNamedParameter')
            ->with('val')
            ->will($this->returnValue(':val'));


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

        $this->builder->expects($this->once())
            ->method('where')
            ->with('feeds.user_id = :user_id')
            ->will($this->returnSelf());

        $andWhereCalls = [['feeds.deleted_at = 0'], ['key = :val']];
        $andWhereIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex) {
                $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                return $this->builder;
            });

        $this->builder->expects($this->exactly(1))
            ->method('setParameter')
            ->with('user_id', 'jack')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                ['id' => 5],
                null
            );

        $result = $this->class->findAllFromUser('jack', ['key' => 'val']);
        $this->assertEquals([Item::fromRow(['id' => 4]), Item::fromRow(['id' => 5])], $result);
    }

    /**
     * @covers \OCA\News\Db\ItemMapperV2::findAll
     */
    public function testFindAll()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('andWhere')
            ->with('feeds.deleted_at = 0')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                ['id' => 5],
                null
            );

        $result = $this->class->findAll();
        $this->assertEquals([Item::fromRow(['id' => 4]), Item::fromRow(['id' => 5])], $result);
    }

    /**
     * @covers \OCA\News\Db\ItemMapperV2::findAllForFeed
     */
    public function testFindAllForFeed()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('feed_id = :feed_identifier')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('setParameter')
            ->with('feed_identifier', 4)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                ['id' => 5],
                null
            );

        $result = $this->class->findAllForFeed(4);
        $this->assertEquals([Item::fromRow(['id' => 4]), Item::fromRow(['id' => 5])], $result);
    }

    public function testFindFromUser()
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

        $this->builder->expects($this->once())
            ->method('where')
            ->with('feeds.user_id = :user_id')
            ->will($this->returnSelf());

        $andWhereCalls = [['items.id = :item_id'], ['feeds.deleted_at = 0']];
        $andWhereIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex) {
                $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                return $this->builder;
            });

        $setParameterCalls = [
            ['user_id', 'jack', 2],  // PDO::PARAM_STR
            ['item_id', 4, 1]  // PDO::PARAM_INT
        ];
        $setParameterIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function (...$args) use (&$setParameterCalls, &$setParameterIndex) {
                $this->assertEquals($setParameterCalls[$setParameterIndex++], $args);
                return $this->builder;
            });

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findFromUser('jack', 4);
        $this->assertEquals(Item::fromRow(['id' => 4]), $result);
    }

    public function testFindByGUIDHash()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('*')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items')
            ->will($this->returnSelf());

        $andWhereCalls = [['feed_id = :feed_id'], ['guid_hash = :guid_hash']];
        $andWhereIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex) {
                $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                return $this->builder;
            });

        $setParameterCalls = [
            ['feed_id', 4, 1],  // PDO::PARAM_INT
            ['guid_hash', 'hash', 2]  // PDO::PARAM_STR
        ];
        $setParameterIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function (...$args) use (&$setParameterCalls, &$setParameterIndex) {
                $this->assertEquals($setParameterCalls[$setParameterIndex++], $args);
                return $this->builder;
            });

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findByGuidHash(4, 'hash');
        $this->assertEquals(Item::fromRow(['id' => 4]), $result);
    }

    public function testFindForUserByGUIDHash()
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

        $andWhereCalls = [['feeds.user_id = :user_id'], ['feeds.id = :feed_id'], ['items.guid_hash = :guid_hash']];
        $andWhereIndex = 0;

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex) {
                $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                return $this->builder;
            });

        $setParameterCalls = [
            ['user_id', 'jack', 2],  // PDO::PARAM_STR
            ['feed_id', 4, 1],  // PDO::PARAM_INT
            ['guid_hash', 'hash', 2]  // PDO::PARAM_STR
        ];
        $setParameterIndex = 0;

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function (...$args) use (&$setParameterCalls, &$setParameterIndex) {
                $this->assertEquals($setParameterCalls[$setParameterIndex++], $args);
                return $this->builder;
            });

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->findForUserByGuidHash('jack', 4, 'hash');
        $this->assertEquals(Item::fromRow(['id' => 4]), $result);
    }

    public function testNewest()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('select')
            ->with('items.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->never())
            ->method('orderBy')
            ->with('items.last_modified', 'DESC')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('addOrderBy')
            ->with('items.id', 'DESC')
            ->willReturnSelf();

        $this->builder->expects($this->exactly(1))
            ->method('where')
            ->with('feeds.user_id = :userId')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameter')
            ->with('userId', 'jack')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                false
            );

        $result = $this->class->newest('jack');
        $this->assertEquals(Item::fromRow(['id' => 4]), $result);
    }

    public function testReadAll()
    {
        $selectbuilder = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->db->expects($this->exactly(2))
            ->method('getQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectbuilder, $this->builder);

        $selectbuilder->expects($this->once())
            ->method('select')
            ->with('items.id')
            ->will($this->returnSelf());

        $selectbuilder->expects($this->once())
            ->method('from')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $selectbuilder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $andWhereCalls = [['feeds.user_id = :userId'], ['items.id <= :maxItemId'], ['items.unread = :unread']];
        $andWhereIndex = 0;

        $selectbuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex, $selectbuilder) {
                $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                return $selectbuilder;
            });

        $setParameterCalls = [['userId', 'admin', null], ['maxItemId', 4, null], ['unread', true, 'boolean']];
        $setParameterIndex = 0;

        $selectbuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function (...$args) use (&$setParameterCalls, &$setParameterIndex, $selectbuilder) {
                $this->assertEquals($setParameterCalls[$setParameterIndex++], $args);
                return $selectbuilder;
            });

        $selectbuilder->expects($this->exactly(1))
            ->method('getSQL')
            ->will($this->returnValue('SQL QUERY'));

        $selectbuilder->expects($this->exactly(1))
            ->method('getParameters')
            ->will($this->returnValue([]));

        $result = $this->getMockBuilder(ResultAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1], ['id' => 2]]);

        $this->db->expects($this->exactly(1))
            ->method('executeQuery')
            ->with('SQL QUERY')
            ->willReturn($result);

        $this->builder->expects($this->exactly(2))
            ->method('createParameter')
            ->will($this->returnArgument(0));

        $this->builder->expects($this->once())
            ->method('update')
            ->with('news_items')
            ->will($this->returnSelf());

        $setCalls = [['unread', 'unread'], ['last_modified', 'last_modified']];
        $setIndex = 0;

        $this->builder->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function (...$args) use (&$setCalls, &$setIndex) {
                $this->assertEquals($setCalls[$setIndex++], $args);
                return $this->builder;
            });

        $this->builder->expects($this->exactly(1))
            ->method('andWhere')
            ->with('id IN (:idList)')
            ->will($this->returnSelf());

        $setParameterCalls2 = [['idList', [1, 2], 101], ['unread', false, 'boolean']];
        $setParameterIndex2 = 0;

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnCallback(function (...$args) use (&$setParameterCalls2, &$setParameterIndex2) {
                if ($setParameterIndex2 < count($setParameterCalls2)) {
                    $this->assertEquals($setParameterCalls2[$setParameterIndex2++], $args);
                } else {
                    // last_modified with dynamic timestamp - just check it's called
                    $this->assertEquals('last_modified', $args[0]);
                    $setParameterIndex2++;
                }
                return $this->builder;
            });

        $this->builder->expects($this->exactly(1))
            ->method('getSQL')
            ->will($this->returnValue('QUERY'));

        $this->builder->expects($this->exactly(1))
            ->method('getParameters')
            ->will($this->returnValue([]));

        $this->builder->expects($this->exactly(1))
            ->method('getParameterTypes')
            ->will($this->returnValue([]));

        $this->db->expects($this->exactly(1))
            ->method('executeStatement')
            ->with('QUERY');

        $this->class->readAll('admin', 4);
    }

    public function testPurgeDeletedEmpty()
    {
        $this->db->expects($this->never())
            ->method('getQueryBuilder');

        $this->class->purgeDeleted('jack', 4);
    }

    public function testDeleteOverThresholdEmptyFeeds()
    {
        $builder1 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func_builder = $this->getMockBuilder(IFunctionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func = $this->getMockBuilder(IQueryFunction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->db->expects($this->exactly(1))
            ->method('getQueryBuilder')
            ->willReturnOnConsecutiveCalls($builder1);

        $builder1->expects($this->exactly(2))
                 ->method('func')
                 ->willReturn($func_builder);

        $func_builder->expects($this->exactly(1))
                 ->method('count')
                 ->with('*', 'itemCount')
                 ->willReturn($func);

        $func_builder->expects($this->exactly(1))
                 ->method('max')
                 ->with('feeds.articles_per_update')
                 ->willReturn($func);

        $builder1->expects($this->once())
                 ->method('select')
                 ->with('feed_id', $func)
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('selectAlias')
                 ->with($func, 'articlesPerUpdate')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('from')
                 ->with('news_items', 'items')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('innerJoin')
                 ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('groupBy')
                 ->with('feed_id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('getSQL')
                 ->willReturn('FEED_SQL');

        $this->class->deleteOverThreshold(1, true);
    }

    public function testDeleteOverThresholdSuccess()
    {
        $builder1 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder2 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder3 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result1 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result2 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result3 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func_builder = $this->getMockBuilder(IFunctionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func = $this->getMockBuilder(IQueryFunction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->db->expects($this->exactly(3))
            ->method('getQueryBuilder')
            ->willReturnOnConsecutiveCalls($builder1, $builder2, $builder3);

        $builder1->expects($this->exactly(2))
                 ->method('func')
                 ->willReturn($func_builder);

        $func_builder->expects($this->exactly(1))
                 ->method('count')
                 ->with('*', 'itemCount')
                 ->willReturn($func);

        $func_builder->expects($this->exactly(1))
            ->method('max')
            ->with('feeds.articles_per_update')
            ->willReturn($func);

        $builder1->expects($this->once())
            ->method('select')
            ->with('feed_id', $func)
            ->willReturnSelf();

        $builder1->expects($this->once())
            ->method('selectAlias')
            ->with($func, 'articlesPerUpdate')
            ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('from')
                 ->with('news_items', 'items')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('innerJoin')
                 ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('groupBy')
                 ->with('feed_id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('getSQL')
                 ->willReturn('FEED_SQL');

        $executeQueryCalls = [
            ['FEED_SQL', [], []],  // executeQuery gets default empty arrays for params and types
            ['RANGE_SQL', ['feedId' => 5], []],
            ['RANGE_SQL', ['feedId' => 1], []]
        ];
        $executeQueryReturns = [$result1, $result2, $result3];
        $executeQueryIndex = 0;

        $this->db->expects($this->exactly(3))
                 ->method('executeQuery')
                 ->willReturnCallback(function (...$args) use (&$executeQueryCalls, &$executeQueryReturns, &$executeQueryIndex) {
                     $this->assertEquals($executeQueryCalls[$executeQueryIndex], $args);
                     return $executeQueryReturns[$executeQueryIndex++];
                 });

        $result1->expects($this->once())
                ->method('fetchAll')
                ->with(2)
                ->willReturn([
                    ['itemCount' => 5, 'articlesPerUpdate' => 5, 'feed_id' => 5],
                    ['itemCount' => 1, 'articlesPerUpdate' => 1, 'feed_id' => 1],
                ]);

        $builder2->expects($this->once())
                 ->method('select')
                 ->with('id')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('from')
                 ->with('news_items')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('where')
                 ->with('feed_id = :feedId')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('andWhere')
                 ->with('starred = false')
                 ->willReturnSelf();

        $builder2->expects($this->never())
                 ->method('orderBy')
                 ->with('last_modified', 'DESC')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('addOrderBy')
                 ->with('id', 'DESC')
                 ->willReturnSelf();

        $builder2->expects($this->exactly(2))
                 ->method('getSQL')
                 ->willReturn('RANGE_SQL');

        $result2->expects($this->once())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([4, 6, 8]);

        $result3->expects($this->once())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([3, 5, 7]);

        $builder3->expects($this->once())
            ->method('delete')
            ->with('news_items')
            ->willReturnSelf();

        $builder3->expects($this->once())
            ->method('where')
            ->with('id IN (?)')
            ->willReturnSelf();

        $builder3->expects($this->exactly(1))
            ->method('getSQL')
            ->willReturn('DELETE_SQL');

        $this->db->expects($this->once())
                 ->method('executeStatement')
                 ->with('DELETE_SQL', [[4, 6, 8, 3, 5, 7]], [101])
                 ->will($this->returnValue(10));

        $res = $this->class->deleteOverThreshold(1, true);
        $this->assertSame(10, $res);
    }

    public function testDeleteOverThresholdSuccessUnread()
    {
        $builder1 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder2 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder3 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result1 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result2 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result3 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func_builder = $this->getMockBuilder(IFunctionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func = $this->getMockBuilder(IQueryFunction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->db->expects($this->exactly(3))
            ->method('getQueryBuilder')
            ->willReturnOnConsecutiveCalls($builder1, $builder2, $builder3);

        $builder1->expects($this->exactly(2))
                 ->method('func')
                 ->willReturn($func_builder);

        $func_builder->expects($this->exactly(1))
                 ->method('count')
                 ->with('*', 'itemCount')
                 ->willReturn($func);

        $func_builder->expects($this->exactly(1))
            ->method('max')
            ->with('feeds.articles_per_update')
            ->willReturn($func);

        $builder1->expects($this->once())
            ->method('select')
            ->with('feed_id', $func)
            ->willReturnSelf();

        $builder1->expects($this->once())
            ->method('selectAlias')
            ->with($func, 'articlesPerUpdate')
            ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('from')
                 ->with('news_items', 'items')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('innerJoin')
                 ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('groupBy')
                 ->with('feed_id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('getSQL')
                 ->willReturn('FEED_SQL');

        $executeQueryCalls = [
            ['FEED_SQL', [], []],  // executeQuery gets default empty arrays for params and types
            ['RANGE_SQL', ['feedId' => 5], []],
            ['RANGE_SQL', ['feedId' => 1], []]
        ];
        $executeQueryReturns = [$result1, $result2, $result3];
        $executeQueryIndex = 0;

        $this->db->expects($this->exactly(3))
                 ->method('executeQuery')
                 ->willReturnCallback(function (...$args) use (&$executeQueryCalls, &$executeQueryReturns, &$executeQueryIndex) {
                     $this->assertEquals($executeQueryCalls[$executeQueryIndex], $args);
                     return $executeQueryReturns[$executeQueryIndex++];
                 });

        $result1->expects($this->once())
                ->method('fetchAll')
                ->with(2)
                ->willReturn([
                    ['itemCount' => 5, 'articlesPerUpdate' => 5, 'feed_id' => 5],
                    ['itemCount' => 1, 'articlesPerUpdate' => 1, 'feed_id' => 1],
                ]);

        $builder2->expects($this->once())
                 ->method('select')
                 ->with('id')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('from')
                 ->with('news_items')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('where')
                 ->with('feed_id = :feedId')
                 ->willReturnSelf();

        $andWhereCalls = [['starred = false'], ['unread = false']];
        $andWhereIndex = 0;

        $builder2->expects($this->exactly(2))
                 ->method('andWhere')
                 ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex, $builder2) {
                     $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                     return $builder2;
                 });
        
        $andWhereCalls = [['starred = false'], ['unread = false']];

        $builder2->expects($this->never())
                 ->method('orderBy')
                 ->with('last_modified', 'DESC')
                 ->willReturnSelf();

        $builder2->expects($this->once())
            ->method('addOrderBy')
            ->with('id', 'DESC')
            ->willReturnSelf();

        $builder2->expects($this->exactly(2))
                 ->method('getSQL')
                 ->willReturn('RANGE_SQL');

        $result2->expects($this->once())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([4, 6, 8]);

        $result3->expects($this->once())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([3, 5, 7]);

        $builder3->expects($this->once())
            ->method('delete')
            ->with('news_items')
            ->willReturnSelf();

        $builder3->expects($this->once())
            ->method('where')
            ->with('id IN (?)')
            ->willReturnSelf();

        $builder3->expects($this->exactly(1))
            ->method('getSQL')
            ->willReturn('DELETE_SQL');

        $this->db->expects($this->once())
                 ->method('executeStatement')
                 ->with('DELETE_SQL', [[4, 6, 8, 3, 5, 7]], [101])
                 ->will($this->returnValue(10));

        $res = $this->class->deleteOverThreshold(1, false);
        $this->assertSame(10, $res);
    }

    public function testDeleteOverThresholdSuccessUnreadSkipsIfUnderThreshold()
    {
        $builder1 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder2 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $builder3 = $this->getMockBuilder(IQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result1 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result2 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result3 = $this->getMockBuilder(IResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func_builder = $this->getMockBuilder(IFunctionBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $func = $this->getMockBuilder(IQueryFunction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->db->expects($this->exactly(3))
            ->method('getQueryBuilder')
            ->willReturnOnConsecutiveCalls($builder1, $builder2, $builder3);

        $builder1->expects($this->exactly(2))
                 ->method('func')
                 ->willReturn($func_builder);

        $func_builder->expects($this->exactly(1))
                 ->method('count')
                 ->with('*', 'itemCount')
                 ->willReturn($func);

        $func_builder->expects($this->exactly(1))
            ->method('max')
            ->with('feeds.articles_per_update')
            ->willReturn($func);

        $builder1->expects($this->once())
            ->method('select')
            ->with('feed_id', $func)
            ->willReturnSelf();

        $builder1->expects($this->once())
            ->method('selectAlias')
            ->with($func, 'articlesPerUpdate')
            ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('from')
                 ->with('news_items', 'items')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('innerJoin')
                 ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('groupBy')
                 ->with('feed_id')
                 ->willReturnSelf();

        $builder1->expects($this->once())
                 ->method('getSQL')
                 ->willReturn('FEED_SQL');

        $executeQueryCalls = [
            ['FEED_SQL', [], []],  // executeQuery gets default empty arrays for params and types
            ['RANGE_SQL', ['feedId' => 5], []]
        ];
        $executeQueryReturns = [$result1, $result2, $result3];
        $executeQueryIndex = 0;

        $this->db->expects($this->exactly(2))
                 ->method('executeQuery')
                 ->willReturnCallback(function (...$args) use (&$executeQueryCalls, &$executeQueryReturns, &$executeQueryIndex) {
                     $this->assertEquals($executeQueryCalls[$executeQueryIndex], $args);
                     return $executeQueryReturns[$executeQueryIndex++];
                 });

        $result1->expects($this->once())
                ->method('fetchAll')
                ->with(2)
                ->willReturn([
                    ['itemCount' => 5, 'articlesPerUpdate' => 5, 'feed_id' => 5],
                    ['itemCount' => 1, 'articlesPerUpdate' => 1, 'feed_id' => 1],
                ]);

        $builder2->expects($this->once())
                 ->method('select')
                 ->with('id')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('from')
                 ->with('news_items')
                 ->willReturnSelf();

        $builder2->expects($this->once())
                 ->method('where')
                 ->with('feed_id = :feedId')
                 ->willReturnSelf();

        $andWhereCalls = [['starred = false'], ['unread = false']];
        $andWhereIndex = 0;

        $builder2->expects($this->exactly(2))
                 ->method('andWhere')
                 ->willReturnCallback(function (...$args) use (&$andWhereCalls, &$andWhereIndex, $builder2) {
                     $this->assertEquals($andWhereCalls[$andWhereIndex++], $args);
                     return $builder2;
                 });

        $builder2->expects($this->never())
                 ->method('orderBy')
                 ->with('last_modified', 'DESC')
                 ->willReturnSelf();

        $builder2->expects($this->once())
            ->method('addOrderBy')
            ->with('id', 'DESC')
            ->willReturnSelf();

        $builder2->expects($this->exactly(1))
                 ->method('getSQL')
                 ->willReturn('RANGE_SQL');

        $result2->expects($this->once())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([4, 6, 8]);

        $result3->expects($this->never())
            ->method('fetchAll')
            ->with(7)
            ->willReturn([3, 5, 7]);

        $builder3->expects($this->once())
            ->method('delete')
            ->with('news_items')
            ->willReturnSelf();

        $builder3->expects($this->once())
            ->method('where')
            ->with('id IN (?)')
            ->willReturnSelf();

        $builder3->expects($this->exactly(1))
            ->method('getSQL')
            ->willReturn('DELETE_SQL');

        $this->db->expects($this->once())
                 ->method('executeStatement')
                 ->with('DELETE_SQL', [[4, 6, 8]], [101])
                 ->will($this->returnValue(10));

        $res = $this->class->deleteOverThreshold(3, false);
        $this->assertSame(10, $res);
    }
}
