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

require_once(__DIR__ . "/../../classloader.php");


class ItemMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $mapper;
	private $items;
	
	public function setUp()
	{
		$this->beforeEach();
		
		$this->mapper = new ItemMapper($this->api);
		
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

 		$this->user = 'john';
 		$this->limit = 10;
 		$this->offset = 3;
 		$this->id = 11;
 		$this->status = 333;
 		$this->updatedSince = 323;

	}
	

	private function makeSelectQuery($prependTo){
		return 'SELECT `items`.* FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ? ' . $prependTo;
	}

	private function makeSelectQueryStatus($prependTo) {
		return $this->makeSelectQuery(
			'AND ((`items`.`status` & ?) = ?) ' .
			$prependTo
		);
	}


	public function testFind(){
		$sql = $this->makeSelectQuery('AND `items`.`id` = ? ');
			
		$this->setMapperResult($sql, array($this->userId, $this->id), $this->row);
		
		$result = $this->mapper->find($this->id, $this->userId);
		$this->assertEquals($this->items[0], $result);
	}

		
	public function testGetStarredCount(){
		$userId = 'john';
		$row = array(
			array('size' => 9)
		);
		$sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
			'WHERE ((`items`.`status` & ?) = ?)';
		
		$this->setMapperResult($sql, array($userId, StatusFlag::STARRED, 
			StatusFlag::STARRED), $row);
		
		$result = $this->mapper->starredCount($userId);
		$this->assertEquals($row[0]['size'], $result);
	}


	public function testReadFeed(){
		$sql = 'UPDATE `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
				'AND `feeds`.`id` = ? ' .
				'AND `items`.`id` <= ? ' .
			'SET `items`.`status` = (`items`.`status` & ?) ';
		$params = array($this->user, 3, 6, ~StatusFlag::UNREAD);
		$this->setMapperResult($sql, $params);
		$this->mapper->readFeed(3, 6, $this->user);
	}


	public function testFindAllNew(){
		$sql = 'AND `items`.`id` >= ?';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status,	$this->status, 
			$this->updatedSince);

		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNew($this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllNewFeed(){
		$sql = 'AND `items`.`feed_id` = ? ' .
				'AND `items`.`id` >= ?';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, $this->id, 
			$this->updatedSince);

		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNewFeed($this->id, $this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllNewFolder(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
				'AND `items`.`id` >= ?';
		$sql = $this->makeSelectQueryStatus($sql);

		$params = array($this->user, $this->status, $this->status, $this->id, 
			$this->updatedSince);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNewFolder($this->id, $this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFeed(){
		$sql = 'AND `items`.`feed_id` = ? ' .
			'AND `items`.`id` > ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, $this->id, 
			$this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFeed($this->id, $this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFeedOffsetZero(){
		$sql = 'AND `items`.`feed_id` = ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, $this->id);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFeed($this->id, $this->limit, 
				0, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFolder(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
			'AND `items`.`id` > ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, $this->id, 
			$this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFolder($this->id, $this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFolderOffsetZero(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, $this->id);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFolder($this->id, $this->limit, 
				0, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAll(){
		$sql = 'AND `items`.`id` > ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status, 
			$this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAll($this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllOffsetZero(){
		$sql = 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($this->user, $this->status, $this->status);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAll($this->limit, 
				0, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindByGuidHash(){
		$hash = md5('test');
		$feedId = 3;
		$sql = $this->makeSelectQuery(
			'AND `items`.`guid_hash` = ? ' .
			'AND `feeds`.`id` = ? ');
			
		$this->setMapperResult($sql, array($this->userId, $hash, $feedId), $this->row);
		
		$result = $this->mapper->findByGuidHash($hash, $feedId, $this->userId);
		$this->assertEquals($this->items[0], $result);
	}


	public function testGetReadOlderThanThreshold(){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT * FROM `*PREFIX*news_items` ' .
			'WHERE NOT ((`status` & ?) > 0)';
		$threshold = 10;
		$feed = new Feed();
		$feed->setId(30);
		$rows = array(array('id' => 30));
		$params = array($status);

		$this->setMapperResult($sql, $params, $rows);
		$result = $this->mapper->getReadOlderThanThreshold($threshold);

		$this->assertEquals($feed->getId(), $result[0]->getId());
	}


	public function testDeleteReadOlderThanId(){
		$id = 10;
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'DELETE FROM `*PREFIX*news_items` WHERE `id` < ? ' .
			'AND NOT ((`status` & ?) > 0)';
		$params = array($id, $status);

		$this->setMapperResult($sql, $params);
		$this->mapper->deleteReadOlderThanId($id);
	}
}