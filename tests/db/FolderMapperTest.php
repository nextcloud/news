<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Db;

require_once(__DIR__ . "/../classloader.php");


class FolderMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $folderMapper;
	private $folders;
	
	protected function setUp(){
		$this->beforeEach();

		$this->folderMapper = new FolderMapper($this->api);

		// create mock folders
		$folder1 = new Folder();
		$folder2 = new Folder();

		$this->folders = array(
			$folder1,
			$folder2
		);
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
		
		$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');
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
		
		$this->setExpectedException('\OCA\AppFramework\Db\MultipleObjectsReturnedException');
		$result = $this->folderMapper->find($id, $userId);
	}



	public function testFindAllFromUser(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `user_id` = ?';
		
		$this->setMapperResult($sql, array($userId), $rows);
		
		$result = $this->folderMapper->findAllFromUser($userId);
		$this->assertEquals($this->folders, $result);
	}


	public function testFindByName(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `user_id` = ?';
		
		$this->setMapperResult($sql, array($userId), $rows);
		
		$result = $this->folderMapper->findAllFromUser($userId);
		$this->assertEquals($this->folders, $result);
	}

}