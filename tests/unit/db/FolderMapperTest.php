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

require_once(__DIR__ . "/../../classloader.php");


class FolderMapperTest extends \OCA\News\Utility\MapperTestUtility {

	private $folderMapper;
	private $folders;
	private $user;
	
	protected function setUp(){
		$this->beforeEach();

		$this->folderMapper = new FolderMapper($this->db);

		// create mock folders
		$folder1 = new Folder();
		$folder2 = new Folder();

		$this->folders = array(
			$folder1,
			$folder2
		);
		$this->user = 'hh';
	}


	public function testFind(){
		$userId = 'john';
		$id = 3;
		$rows = array(
		  array('id' => $this->folders[0]->getId()),
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$result = $this->folderMapper->find($id, $userId);
		$this->assertEquals($this->folders[0], $result);
		
	}


	public function testFindNotFound(){
		$userId = 'john';
		$id = 3;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId));
		
		$this->setExpectedException('\OCA\News\Db\DoesNotExistException');
		$result = $this->folderMapper->find($id, $userId);	
	}
	

	public function testFindMoreThanOneResultFound(){
		$userId = 'john';
		$id = 3;
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$this->setExpectedException('\OCA\News\Db\MultipleObjectsReturnedException');
		$result = $this->folderMapper->find($id, $userId);
	}



	public function testFindAllFromUser(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `user_id` = ? ' .
			'AND `deleted_at` = 0';
		
		$this->setMapperResult($sql, array($userId), $rows);
		
		$result = $this->folderMapper->findAllFromUser($userId);
		$this->assertEquals($this->folders, $result);
	}


	public function testFindByName(){
		$folderName = 'heheh';
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `name` = ? ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($folderName, $userId), $rows);
		
		$result = $this->folderMapper->findByName($folderName, $userId);
		$this->assertEquals($this->folders, $result);
	}


	public function testDelete(){
		$folder = new Folder();
		$folder->setId(3);

		$sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `id` = ?';
		$arguments = array($folder->getId());

		$sql2 = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';

		$sql3 = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN '.
			'(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';
		$arguments2 = array($folder->getId());

		$this->setMapperResult($sql, $arguments);
		$this->setMapperResult($sql2, $arguments2);
		$this->setMapperResult($sql3);

		$this->folderMapper->delete($folder);
	}


	public function testGetPurgeDeleted(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ';
		$this->setMapperResult($sql, array($deleteOlderThan), $rows);
		$result = $this->folderMapper->getToDelete($deleteOlderThan);

		$this->assertEquals($this->folders, $result);
	}



	public function testGetPurgeDeletedUser(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($deleteOlderThan, $this->user), $rows);
		$result = $this->folderMapper->getToDelete($deleteOlderThan, $this->user);

		$this->assertEquals($this->folders, $result);
	}


	public function testGetAllPurgeDeletedUser(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($this->user), $rows);
		$result = $this->folderMapper->getToDelete(null, $this->user);

		$this->assertEquals($this->folders, $result);
	}


	public function testDeleteFromUser(){
		$userId = 'john';
		$sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';

		$this->setMapperResult($sql, array($userId));

		$this->folderMapper->deleteUser($userId);
	}


}