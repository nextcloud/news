<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Db;


class FolderMapperTest extends \OCA\News\Tests\Unit\Db\MapperTestUtility {

    private $folderMapper;
    private $folders;
    private $user;

    protected function setUp(){
        parent::setUp();

        $this->folderMapper = new FolderMapper($this->db);

        // create mock folders
        $folder1 = new Folder();
        $folder2 = new Folder();

        $this->folders = [$folder1, $folder2];
        $this->user = 'hh';
        $this->twoRows = [
            ['id' => $this->folders[0]->getId()],
            ['id' => $this->folders[1]->getId()]
        ];
    }


    public function testFind(){
        $userId = 'john';
        $id = 3;
        $rows = [['id' => $this->folders[0]->getId()]];
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId], $rows);

        $result = $this->folderMapper->find($id, $userId);
        $this->assertEquals($this->folders[0], $result);

    }


    public function testFindNotFound(){
        $userId = 'john';
        $id = 3;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId]);

        $this->setExpectedException(
            '\OCP\AppFramework\Db\DoesNotExistException'
        );
        $this->folderMapper->find($id, $userId);
    }


    public function testFindMoreThanOneResultFound(){
        $userId = 'john';
        $id = 3;
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        $this->setMapperResult($sql, [$id, $userId], $rows);

        $this->setExpectedException(
            '\OCP\AppFramework\Db\MultipleObjectsReturnedException'
        );
        $this->folderMapper->find($id, $userId);
    }



    public function testFindAllFromUser(){
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `user_id` = ? ' .
            'AND `deleted_at` = 0';

        $this->setMapperResult($sql, [$userId], $rows);

        $result = $this->folderMapper->findAllFromUser($userId);
        $this->assertEquals($this->folders, $result);
    }


    public function testFindByName(){
        $folderName = 'heheh';
        $userId = 'john';
        $rows = $this->twoRows;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `name` = ? ' .
            'AND `user_id` = ?';

        $this->setMapperResult($sql, [$folderName, $userId], $rows);

        $result = $this->folderMapper->findByName($folderName, $userId);
        $this->assertEquals($this->folders, $result);
    }


    public function testDelete(){
        $folder = new Folder();
        $folder->setId(3);

        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `id` = ?';
        $arguments = [$folder->getId()];

        $sql2 = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';
        $arguments2 = [$folder->getId()];

        $sql3 = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN '.
            '(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';

        $this->setMapperResult($sql, $arguments, [], null, null, true);
        $this->setMapperResult($sql2, $arguments2, [], null, null, true);
        $this->setMapperResult($sql3, [], [], null, null, true);

        $this->folderMapper->delete($folder);
    }


    public function testGetPurgeDeleted(){
        $rows = $this->twoRows;
        $deleteOlderThan = 110;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `deleted_at` < ? ';
        $this->setMapperResult($sql, [$deleteOlderThan], $rows);
        $result = $this->folderMapper->getToDelete($deleteOlderThan);

        $this->assertEquals($this->folders, $result);
    }



    public function testGetPurgeDeletedUser(){
        $rows = $this->twoRows;
        $deleteOlderThan = 110;
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `deleted_at` < ? ' .
            'AND `user_id` = ?';
        $this->setMapperResult($sql, [$deleteOlderThan, $this->user], $rows);
        $result = $this->folderMapper->getToDelete(
            $deleteOlderThan, $this->user
        );

        $this->assertEquals($this->folders, $result);
    }


    public function testGetAllPurgeDeletedUser(){
        $rows = $this->twoRows;

        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ' .
            'AND `user_id` = ?';
        $this->setMapperResult($sql, [$this->user], $rows);
        $result = $this->folderMapper->getToDelete(null, $this->user);

        $this->assertEquals($this->folders, $result);
    }


    public function testDeleteFromUser(){
        $userId = 'john';
        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';

        $this->setMapperResult($sql, [$userId]);

        $this->folderMapper->deleteUser($userId);
    }


}