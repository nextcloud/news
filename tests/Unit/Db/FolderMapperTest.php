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
use OCA\News\Db\FolderMapper;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class FolderMapperTest extends MapperTestUtility
{
    /** @var FolderMapper */
    private $folderMapper;
    /** @var Folder[] */
    private $folders;
    /** @var string */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folderMapper = new FolderMapper($this->db, new Time());

        // create mock folders
        $folder1 = new Folder();
        $folder1->setId(4);
        $folder1->resetUpdatedFields();
        $folder2 = new Folder();
        $folder2->setId(5);
        $folder2->resetUpdatedFields();

        $this->folders = [$folder1, $folder2];
        $this->user = 'hh';
        $this->twoRows = [
            ['id' => $this->folders[0]->getId()],
            ['id' => $this->folders[1]->getId()]
        ];
    }

    /**
     * @covers \OCA\News\Db\FolderMapper::find
     */
    public function testFind()
    {
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(2))
                    ->method('fetch')
                    ->willReturnOnConsecutiveCalls(['id' => 4], false);

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, 3, 1], [2, $userId, 2]);

        $this->query->expects($this->exactly(1))
                    ->method('closeCursor');

        $this->query->expects($this->once())
                    ->method('execute')
                    ->with('')
                    ->will($this->returnValue([]));

        $result = $this->folderMapper->find($userId, $id);
        $this->assertEquals($this->folders[0], $result);
    }


    public function testFindNotFound()
    {
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(1))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(false);

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, $id, 1], [2, $userId, 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->with('')
            ->will($this->returnValue([]));

        $this->expectException(DoesNotExistException::class);
        $this->folderMapper->find($userId, $id);
    }


    public function testFindMoreThanOneResultFound()
    {
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, 3, 1], [2, $userId, 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->with('')
            ->will($this->returnValue([]));

        $this->expectException(MultipleObjectsReturnedException::class);
        $this->folderMapper->find($userId, $id);
    }



    public function testFindAllFromUser()
    {
        $userId = 'john';
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `user_id` = ? ' .
            'AND `deleted_at` = 0';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(1))
            ->method('bindValue')
            ->withConsecutive([1, $userId, 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $result = $this->folderMapper->findAllFromUser($userId);
        $this->assertEquals($this->folders, $result);
    }


    public function testFindByName()
    {
        $folderName = 'heheh';
        $userId = 'john';
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `name` = ? ' .
            'AND `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, $folderName, 2], [2, $userId, 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $result = $this->folderMapper->findByName($folderName, $userId);
        $this->assertEquals($this->folders, $result);
    }


    public function testDelete()
    {
        $folder = new Folder();
        $folder->setId(3);

        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `id` = ?';
        $arguments = [$folder->getId()];

        $sql2 = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';
        $arguments2 = [$folder->getId()];

        $sql3 = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN '.
            '(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';

        $this->db->expects($this->exactly(3))
            ->method('prepare')
            ->withConsecutive(
                [$sql, null, null],
                [$sql2, null, null],
                [$sql3, null, null]
            )
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, 3, 1]);

        $this->query->expects($this->exactly(3))
            ->method('closeCursor');

        $this->query->expects($this->exactly(3))
            ->method('execute')
            ->will($this->returnValue([]));

        $this->folderMapper->delete($folder);
    }


    public function testGetPurgeDeleted()
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `deleted_at` < ? ';


        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(1))
            ->method('bindValue')
            ->withConsecutive([1, 110, 1]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $result = $this->folderMapper->getToDelete(110);

        $this->assertEquals($this->folders, $result);
    }



    public function testGetPurgeDeletedUser()
    {
        $deleteOlderThan = 110;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `deleted_at` < ? ' .
            'AND `user_id` = ?';


        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive([1, 110, 1], [2, 'hh', 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $result = $this->folderMapper->getToDelete(
            $deleteOlderThan, $this->user
        );

        $this->assertEquals($this->folders, $result);
    }


    public function testGetAllPurgeDeletedUser()
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['id' => 4], ['id' => 5]);

        $this->query->expects($this->exactly(1))
            ->method('bindValue')
            ->withConsecutive([1, 'hh', 2]);

        $this->query->expects($this->exactly(1))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $result = $this->folderMapper->getToDelete(null, $this->user);

        $this->assertEquals($this->folders, $result);
    }


    public function testDeleteFromUser()
    {
        $userId = 'john';
        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';

        $this->db->expects($this->exactly(1))
            ->method('prepare')
            ->with($sql, null, null)
            ->will(($this->returnValue($this->query)));

        $this->query->expects($this->never())
            ->method('fetch');

        $this->query->expects($this->exactly(1))
            ->method('bindValue')
            ->withConsecutive([1, $userId, 2]);

        $this->query->expects($this->exactly(0))
            ->method('closeCursor');

        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([]));

        $this->folderMapper->deleteUser($userId);
    }


}