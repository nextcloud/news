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

use OCA\News\Db\Folder;
use OCA\News\Db\FolderMapperV2;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class FolderMapperTest extends MapperTestUtility
{
    /** @var FolderMapperV2 */
    private $class;
    /** @var Folder[] */
    private $folders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new FolderMapperV2($this->db, new Time());

        // create mock folders
        $folder1 = new Folder();
        $folder1->setId(4);
        $folder1->resetUpdatedFields();
        $folder2 = new Folder();
        $folder2->setId(5);
        $folder2->resetUpdatedFields();

        $this->folders = [$folder1, $folder2];
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::findAllFromUser
     */
    public function testFindAllFromUser()
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
                      ->with('news_folders')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('where')
                      ->with('user_id = :user_id')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('andWhere')
                      ->with('deleted_at = 0')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('setParameter')
                      ->with('user_id', 'jack')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('execute')
                      ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(3))
                     ->method('fetch')
                     ->willReturnOnConsecutiveCalls(
                         ['id' => 4],
                         ['id' => 5],
                         null
                     );

        $result = $this->class->findAllFromUser('jack', []);
        $this->assertEquals($this->folders, $result);
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::findFromUser
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
                      ->with('news_folders')
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('where')
                      ->with('user_id = :user_id')
                      ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
                      ->method('andWhere')
                      ->withConsecutive(['id = :id'], ['deleted_at = 0'])
                      ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
                      ->method('setParameter')
                      ->withConsecutive(['user_id', 'jack'], ['id', 1])
                      ->will($this->returnSelf());

        $this->builder->expects($this->once())
                      ->method('execute')
                      ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(2))
                     ->method('fetch')
                     ->willReturnOnConsecutiveCalls(
                         ['id' => 4],
                         false
                     );

        $result = $this->class->findFromUser('jack', 1);
        $this->assertEquals($this->folders[0], $result);
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::findFromUser
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
            ->with('news_folders')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['id = :id'], ['deleted_at = 0'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['id', 1])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(1))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                false
            );

        $this->expectException(DoesNotExistException::class);
        $this->class->findFromUser('jack', 1);
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::findFromUser
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
            ->with('news_folders')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('user_id = :user_id')
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['id = :id'], ['deleted_at = 0'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['user_id', 'jack'], ['id', 1])
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

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
     * @covers \OCA\News\Db\FolderMapperV2::findAll
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
            ->with('news_folders')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('where')
            ->with('deleted_at = 0')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->cursor));

        $this->cursor->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['id' => 4],
                ['id' => 5],
                null
            );

        $result = $this->class->findAll();
        $this->assertEquals($this->folders, $result);
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::read
     */
    public function testRead()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('update')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('setValue')
            ->with('unread', 0)
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('andWhere')
            ->withConsecutive(['feeds.user_id = :userId'], ['feeds.folder_id = :folderId'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['userId', 'admin'], ['folderId', 1])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('getSQL')
            ->will($this->returnValue('QUERY'));

        $this->db->expects($this->exactly(1))
            ->method('executeUpdate')
            ->with('QUERY');

        $this->class->read('admin', 1);
    }

    /**
     * @covers \OCA\News\Db\FolderMapperV2::read
     */
    public function testReadWithMaxId()
    {
        $this->db->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($this->builder);

        $this->builder->expects($this->once())
            ->method('update')
            ->with('news_items', 'items')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('innerJoin')
            ->with('items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id')
            ->will($this->returnSelf());

        $this->builder->expects($this->once())
            ->method('setValue')
            ->with('unread', 0)
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('andWhere')
            ->withConsecutive(['feeds.user_id = :userId'], ['feeds.folder_id = :folderId'], ['items.id =< :maxItemId'])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(3))
            ->method('setParameter')
            ->withConsecutive(['userId', 'admin'], ['folderId', 1], ['maxItemId', 4])
            ->will($this->returnSelf());

        $this->builder->expects($this->exactly(1))
            ->method('getSQL')
            ->will($this->returnValue('QUERY'));

        $this->db->expects($this->exactly(1))
            ->method('executeUpdate')
            ->with('QUERY');

        $this->class->read('admin', 1, 4);
    }
}