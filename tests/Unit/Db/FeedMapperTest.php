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

use OC\DB\QueryBuilder\Parameter;
use OC\DB\ResultAdapter;
use OCA\News\Db\Feed;
use OCA\News\Db\FeedMapperV2;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IFunctionBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\QueryBuilder\IQueryFunction;

class FeedMapperTest extends MapperTestUtility
{
    /** @var FeedMapperV2 */
    private $class;
    /** @var Feed[] */
    private $feeds;

    /**
     * @covers \OCA\News\Db\FeedMapperV2::__construct
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new FeedMapperV2($this->db, new Time());

        // create mock folders
        $feed1 = new Feed();
        $feed1->setId(4);
        $feed1->resetUpdatedFields();
        $feed2 = new Feed();
        $feed2->setId(5);
        $feed2->resetUpdatedFields();

        $this->feeds = [$feed1, $feed2];
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::__construct
     */
    public function testSetUpSuccess(): void
    {
        $this->assertEquals('news_feeds', $this->class->getTableName());
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findAllFromUser
     */
    public function testFindAllFromUser()
    {
        $this->db->expects($this->once())
                 ->method('getQueryBuilder')
                 ->willReturn($this->builder);

        $funcbuilder = $this->getMockBuilder(IFunctionBuilder::class)
                            ->getMock();

        $func = $this->getMockBuilder(IQueryFunction::class)
                     ->getMock();

        $funcbuilder->expects($this->once())
                    ->method('count')
                    ->with('items.id', 'unreadCount')
                    ->will($this->returnValue($func));

        $this->builder->expects($this->once())
                      ->method('func')
                      ->will($this->returnValue($funcbuilder));

        $this->builder->expects($this->once())
                      ->method('select')
                      ->with('feeds.*', $func)
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('from')
                      ->with('news_feeds', 'feeds')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('leftJoin')
                      ->with('feeds', 'news_items', 'items', 'items.feed_id = feeds.id AND items.unread = :unread')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('where')
                      ->with('feeds.user_id = :user_id')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('andWhere')
                      ->with('feeds.deleted_at = 0')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('groupby')
                      ->with('feeds.id')
                      ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
                      ->method('setParameter')
                      ->withConsecutive(['unread', true], ['user_id', 'jack'])
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
        $this->assertEquals($this->feeds, $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findFromUser
     */
    public function testFindFromUser()
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
                      ->with('news_feeds')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('where')
                      ->with('user_id = :user_id')
                      ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
                      ->method('andWhere')
                      ->withConsecutive(['id = :id'])
                      ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
                      ->method('setParameter')
                      ->withConsecutive(['user_id', 'jack'], ['id', 1])
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

        $result = $this->class->findFromUser('jack', 1);
        $this->assertEquals($this->feeds[0], $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findFromUser
     */
    public function testFindFromUserEmpty()
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('andWhere')
            ->withConsecutive(['id = :id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['id', 1])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(1))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                false
            );

        $this->expectException(DoesNotExistException::class);
        $this->class->findFromUser('jack', 1);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findByURL
     */
    public function testFindByUrl()
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('andWhere')
            ->withConsecutive(['url = :url'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['url', 'https://url.com'])
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

        $result = $this->class->findByURL('jack', 'https://url.com');
        $this->assertEquals($this->feeds[0], $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findFromUser
     */
    public function testFindFromUserDuplicate()
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('andWhere')
            ->withConsecutive(['id = :id'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['id', 1])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->cursor);

        $this->cursor->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1],
                ['id' => 2]
            );

        $this->expectException(MultipleObjectsReturnedException::class);
        $this->class->findFromUser('jack', 1);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findAll
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('deleted_at = 0')
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
        $this->assertEquals($this->feeds, $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findAllFromFolder
     */
    public function testFindAllFromFolder()
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('folder_id = :folder_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('setParameter')
            ->withConsecutive(['folder_id', 1])
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

        $result = $this->class->findAllFromFolder(1);
        $this->assertEquals($this->feeds, $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::findAllFromFolder
     */
    public function testFindAllFromRootFolder()
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
            ->with('news_feeds')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('folder_id IS NULL')
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

        $result = $this->class->findAllFromFolder(null);
        $this->assertEquals($this->feeds, $result);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::read
     */
    public function testRead()
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

        $selectbuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['feeds.user_id = :userId'], ['feeds.id = :feedId'])
            ->will($this->returnSelf());

        $selectbuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'admin'], ['feedId', 1])
            ->will($this->returnSelf());

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

        $this->builder->expects($this->once())
            ->method('update')
            ->with('news_items')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('createParameter')
            ->will($this->returnArgument(0));

        $this->builder->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(['unread', 'unread'], ['last_modified', 'last_modified'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['id IN (:idList)'], ['unread != :unread'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['unread', false], ['idList', [1, 2]], ['last_modified'])
            ->will($this->returnSelf());
        
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

        $this->class->read('admin', 1);
    }

    /**
     * @covers \OCA\News\Db\FeedMapperV2::read
     */
    public function testReadWithMaxID()
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

        $selectbuilder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(['feeds.user_id = :userId'], ['feeds.id = :feedId'], ['items.id <= :maxItemId'])
            ->will($this->returnSelf());

        $selectbuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['userId', 'admin'], ['feedId', 1], ['maxItemId', 4])
            ->will($this->returnSelf());

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

        $this->builder->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(['unread', 'unread'], ['last_modified', 'last_modified'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['id IN (:idList)'], ['unread != :unread'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['unread', false], ['idList', [1, 2]], ['last_modified'])
            ->will($this->returnSelf());

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

        $this->class->read('admin', 1, 4);
    }
}
