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


class ItemMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $itemMapper;
	private $items;
	
	public function setUp()
	{
		$this->beforeEach();
		
		$this->itemMapper = new ItemMapper($this->api);
		
		// create mock items
		$item1 = new Item();
		$item2 = new Item();

		$this->items = array(
			$item1,
			$item2
		);
		
		$this->userId = 'john';
		$this->id = 3;
		$this->folderId = 2;
		
		$this->row = array(
		    array('id' => $this->items[0]->getId()),
 		);
 		
 		$this->rows = array(
 			array('id' => $this->items[0]->getId()),
 			array('id' => $this->items[1]->getId())
 		);
		
	}
	
	public function testFind(){
		$sql = 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
			'JOIN `*dbprefix*news_feeds` ' .
			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
			'WHERE `*dbprefix*news_items`.`id` = ? ' .
			'AND `*dbprefix*news_feeds`.`user_id` = ? ';
			
		$this->setMapperResult($sql, array($this->id, $this->userId), $this->row);
		
		$result = $this->itemMapper->find($this->id, $this->userId);
		$this->assertEquals($this->items[0], $result);
		
	}


	public function testFindByUrlHash(){
		$urlHash = md5('hihi');
		$sql = 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
			'JOIN `*dbprefix*news_feeds` ' .
			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
			'WHERE `*dbprefix*news_items`.`url_hash` = ? ' .
			'AND `*dbprefix*news_feeds`.`user_id` = ? ';
		$this->setMapperResult($sql, array($urlHash, $this->userId), $this->row);
		
		$result = $this->itemMapper->findByUrlHash($urlHash, $this->userId);
		$this->assertEquals($this->items[0], $result);
	}

// 
// 	public function testFindNotFound(){
// 		$sql = 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
// 			'JOIN `*dbprefix*news_feeds` ' .
// 			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
// 			'WHERE `*dbprefix*news_items`.`id` = ? ' .
// 			'AND `*dbprefix*news_feeds`.`user_id` = ? ';
// 			
// 		$this->setMapperResult($sql, array($id, $userId));
// 		
// 		$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');
// 		$result = $this->itemMapper->find($id, $userId);	
// 	}
// 	
// 	public function testFindMoreThanOneResultFound(){
// 		$rows = array(
// 			array('id' => $this->items[0]->getId()),
// 			array('id' => $this->items[1]->getId())
// 		);
// 		$sql = 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
// 			'JOIN `*dbprefix*news_feeds` ' .
// 			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
// 			'WHERE `*dbprefix*news_items`.`id` = ? ' .
// 			'AND `*dbprefix*news_feeds`.`user_id` = ? ';
// 
// 		
// 		$this->setMapperResult($sql, array($id, $userId), $rows);
// 		
// 		$this->setExpectedException('\OCA\AppFramework\Db\MultipleObjectsReturnedException');
// 		$result = $this->itemMapper->find($id, $userId);
// 	}
// 	
// 	public function testFindAllFromFeed(){
// 		$userId = 'john';
// 		$feedId = 3;
// 		$rows = array(
// 			array('id' => $this->items[0]->getId()),
// 			array('id' => $this->items[1]->getId())
// 		);
// 		$sql = 'SELECT * FROM `*PREFIX*news_items` ' .
// 			'WHERE user_id = ? ' .
// 			'AND feed_id = ?';
// 
// 		$this->setMapperResult($sql, array($feedId, $userId), $rows);
// 		$result = $this->itemMapper->findAllFromFeed($feedId, $userId);
// 		$this->assertEquals($this->items, $result);
// 
// 	}
// 	
// 	public function testFindAllFromFeedByStatus(){
// 		$userId = 'john';
// 		$feedId = 3;
// 		$status = 2;
// 		$rows = array(
// 			array('id' => $this->items[0]->getId()),
// 			array('id' => $this->items[1]->getId())
// 		);
// 		$sql = 'SELECT * FROM `*PREFIX*news_items` ' .
// 			'WHERE user_id = ? ' .
// 			'AND feed_id = ? ' .
// 			'AND ((`*dbprefix*news_items`.`status` & ?) > 0)';
// 
// 		$this->setMapperResult($sql, array($feedId, $userId, $status), $rows);
// 		$result = $this->itemMapper->findAllFromFeedByStatus($feedId, $userId, $status);
// 		$this->assertEquals($this->items, $result);
// 
// 	}
	
	private function makeFindAllFromFolderQuery($custom) {
		return 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
			'JOIN `*dbprefix*news_feeds` ' .
			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
			'WHERE `*dbprefix*news_feeds`.`user_id` = ? ' .
			'AND `*dbprefix*news_feeds`.`folder_id` = ? ' .
			'AND ((`*dbprefix*news_items`.`status` & ?) > 0) ' .
			$custom;
	}
	
	public function testFindAllFromFolder() {
		$sql = $this->makeFindAllFromFolderQuery('');
		
		$status = 2;
		
		$params = array($this->userId, $this->folderId, $status);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->itemMapper->findAllFromFolderByOffset($this->userId, $this->folderId, $status);
		$this->assertEquals($this->items, $result);
		
	}
	
	public function testFindAllFromFolderByOffset() {
		$sql = $this->makeFindAllFromFolderQuery('');
		
		$status = 2;
		$limit = 10;
		$offset = 10;
		
		$params = array($this->userId, $this->folderId, $status);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->itemMapper->findAllFromFolderByOffset($this->userId, $this->folderId, $status, $limit, $offset);
		$this->assertEquals($this->items, $result);
		
	}
	
	public function testFindAllFromFolderByLastModified() {
		$sql = $this->makeFindAllFromFolderQuery(' AND (`*dbprefix*news_items`.`last_modified` >= ?)');
		
		$status = 2;
		$lastModified = 100;
		
		$params = array($this->userId, $this->folderId, $status, $lastModified);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->itemMapper->findAllFromFolderByLastMofified($this->userId, $this->folderId, $status, $lastModified);
		$this->assertEquals($this->items, $result);
		
	}
	
// 	}
// 	
// 	public function testFindAllFromFolderByLastModified() {
// 		$userId = 'john';
// 		$folderId = 3;
// 		$lastModified = 123;
// 	
// 		$sql = 'SELECT `*dbprefix*news_items`.* FROM `*dbprefix*news_items` ' .
// 			'JOIN `*dbprefix*news_feeds` ' .
// 			'ON `*dbprefix*news_feeds`.`id` = `*dbprefix*news_items`.`feed_id` ' .
// 			'WHERE `*dbprefix*news_feeds`.`user_id` = ? ' .
// 			'AND `*dbprefix*news_feeds`.`folder_id` = ? ' .
// 			'AND `*dbprefix*news_items`.last_modified >= ? ';
// 			
// 		$this->setMapperResult($sql, array($userId, $folderId, $lastModified));
// 		$result = $this->itemMapper->findAllFromFolderByLastMofified($userId, $folderId, $lastModified);
// 	}
	

}