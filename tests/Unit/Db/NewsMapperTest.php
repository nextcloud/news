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

use OCA\News\Db\Feed;
use OCA\News\Db\NewsMapperV2;
use OCA\News\Utility\Time;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use PHPUnit\Framework\TestCase;

/**
 * Class TmpNewsMapper
 *
 * @package OCA\News\Tests\Unit\Db
 */
abstract class TmpNewsMapper extends NewsMapperV2
{
    const TABLE_NAME = 'NAME';
}

/**
 * Class NewsMapperTest
 *
 * @package OCA\News\Tests\Unit\Db
 */
class NewsMapperTest extends TestCase
{
    /** @var IDBConnection */
    private $db;
    /** @var Time */
    private $time;
    /** @var NewsMapperV2 */
    private $class;

    /**
     * @covers \OCA\News\Db\NewsMapperV2::__construct
     */
    protected function setUp(): void
    {
        $this->db = $this->getMockBuilder(IDBConnection::class)
                         ->getMock();
        $this->time = $this->getMockBuilder(Time::class)
                           ->getMock();

        $this->class = $this->getMockBuilder(TmpNewsMapper::class)
                            ->setConstructorArgs([$this->db, $this->time, 'entity'])
                            ->getMockForAbstractClass();
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::__construct
     */
    public function testSetUpSuccess(): void
    {
        $this->assertEquals('NAME', $this->class->getTableName());
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::update
     */
    public function testUpdateNoChange()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();

        $this->time->expects($this->never())
                   ->method('getMicroTime')
                   ->willReturn('1');

        $feed->expects($this->never())
             ->method('setLastModified')
             ->with('1');

        $feed->expects($this->exactly(2))
             ->method('getUpdatedFields')
             ->willReturn([]);

        $result = $this->class->update($feed);
        $this->assertEquals($feed, $result);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::update
     */
    public function testUpdateChange()
    {
        $this->expectException('InvalidArgumentException');

        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();

        $this->time->expects($this->once())
                   ->method('getMicroTime')
                   ->willReturn('1');

        $feed->expects($this->once())
             ->method('setLastModified')
             ->with('1');

        $feed->expects($this->exactly(2))
             ->method('getUpdatedFields')
             ->willReturn(['a' => 'b']);

        $result = $this->class->update($feed);
        $this->assertEquals($feed, $result);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::insert
     */
    public function testInsert()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();
        $qb = $this->getMockBuilder(IQueryBuilder::class)
                     ->getMock();

        $this->time->expects($this->once())
                   ->method('getMicroTime')
                   ->willReturn('1');

        $this->db->expects($this->once())
                   ->method('getQueryBuilder')
                   ->willReturn($qb);

        $feed->expects($this->once())
             ->method('setLastModified')
             ->with('1');

        $feed->expects($this->once())
             ->method('getUpdatedFields')
             ->willReturn([]);

        $result = $this->class->insert($feed);
        $this->assertEquals($feed, $result);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::purgeDeleted
     */
    public function testPurgeEmptyAll()
    {
        $qb = $this->getMockBuilder(IQueryBuilder::class)
                     ->getMock();

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $qb->expects($this->once())
            ->method('delete')
            ->with('NAME')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('andWhere')
            ->with('deleted_at != 0')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('executeStatement');

        $result = $this->class->purgeDeleted(null, null);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::purgeDeleted
     */
    public function testPurgeUser()
    {
        $qb = $this->getMockBuilder(IQueryBuilder::class)
                     ->getMock();

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $qb->expects($this->once())
            ->method('delete')
            ->with('NAME')
            ->will($this->returnSelf());

        $qb->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['deleted_at != 0'], ['user_id = :user_id'])
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('setParameter')
            ->with('user_id', 'jack')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('executeStatement');

        $result = $this->class->purgeDeleted('jack', null);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::purgeDeleted
     */
    public function testPurgeTime()
    {
        $qb = $this->getMockBuilder(IQueryBuilder::class)
                     ->getMock();

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $qb->expects($this->once())
            ->method('delete')
            ->with('NAME')
            ->will($this->returnSelf());

        $qb->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['deleted_at != 0'], ['deleted_at < :deleted_at'])
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('setParameter')
            ->with('deleted_at', 1)
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('executeStatement');

        $result = $this->class->purgeDeleted(null, 1);
    }

    /**
     * @covers \OCA\News\Db\NewsMapperV2::purgeDeleted
     */
    public function testPurgeBoth()
    {
        $qb = $this->getMockBuilder(IQueryBuilder::class)
                     ->getMock();

        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $qb->expects($this->once())
            ->method('delete')
            ->with('NAME')
            ->will($this->returnSelf());

        $qb->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(['deleted_at != 0'], ['user_id = :user_id'], ['deleted_at < :deleted_at'])
            ->will($this->returnSelf());

        $qb->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['deleted_at', 1])
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('executeStatement');

        $result = $this->class->purgeDeleted('jack', 1);
    }
}
