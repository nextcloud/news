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
	private $newestItemId;
	private $limit;
	private $user;
	private $offset;
	private $updatedSince;
	private $status;

	
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
 		$this->newestItemId = 2;

	}
	

	private function makeSelectQuery($prependTo){
		return 'SELECT `items`.* FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ? ' . $prependTo;
	}

	private function makeSelectQueryStatus($prependTo, $status) {
		$status = (int) $status;

		return $this->makeSelectQuery(
			'AND ((`items`.`status` & ' . $status . ') = ' . $status . ') ' .
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
			'WHERE ((`items`.`status` & ' . StatusFlag::STARRED . ') = '
				. StatusFlag::STARRED . ')';
		
		$this->setMapperResult($sql, array($userId), $row);
		
		$result = $this->mapper->starredCount($userId);
		$this->assertEquals($row[0]['size'], $result);
	}


	public function testReadAll(){
		$sql = 'UPDATE `*PREFIX*news_items` ' . 
			'SET `status` = `status` & ? ' .
			'WHERE `id` IN (' .
				'SELECT `items`.`id` FROM `*PREFIX*news_items` `items` ' .
				'JOIN `*PREFIX*news_feeds` `feeds` ' .
					'ON `feeds`.`id` = `items`.`feed_id` '.
					'AND `items`.`id` <= ? ' .
					'AND `feeds`.`user_id` = ? ' .
				') ';
		$params = array(~StatusFlag::UNREAD, 3, $this->user);
		$this->setMapperResult($sql, $params);
		$this->mapper->readAll(3, $this->user);
	}	


	public function testReadFolder(){
		$sql = 'UPDATE `*PREFIX*news_items` ' . 
			'SET `status` = `status` & ? ' .
			'WHERE `id` IN (' .
				'SELECT `items`.`id` FROM `*PREFIX*news_items` `items` ' .
				'JOIN `*PREFIX*news_feeds` `feeds` ' .
					'ON `feeds`.`id` = `items`.`feed_id` '.
					'AND `feeds`.`folder_id` = ? ' .
					'AND `items`.`id` <= ? ' .
					'AND `feeds`.`user_id` = ? ' .
				') ';
		$params = array(~StatusFlag::UNREAD, 3, 6, $this->user);
		$this->setMapperResult($sql, $params);
		$this->mapper->readFolder(3, 6, $this->user);
	}


	public function testReadFeed(){
		$sql = 'UPDATE `*PREFIX*news_items` ' . 
			'SET `status` = `status` & ? ' .
				'WHERE `feed_id` = ? ' .
				'AND `id` <= ? ' .
				'AND EXISTS (' .
					'SELECT * FROM `*PREFIX*news_feeds` ' .
					'WHERE `user_id` = ? ' .
					'AND `id` = ? ) ';
		$params = array(~StatusFlag::UNREAD, 3, 6, $this->user, 3);
		$this->setMapperResult($sql, $params);
		$this->mapper->readFeed(3, 6, $this->user);
	}


	public function testFindAllNew(){
		$sql = 'AND `items`.`last_modified` >= ?';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->updatedSince);

		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNew($this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllNewFolder(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
				'AND `items`.`last_modified` >= ?';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);

		$params = array($this->user, $this->id, $this->updatedSince);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNewFolder($this->id, $this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllNewFeed(){
		$sql = 'AND `items`.`feed_id` = ? ' .
				'AND `items`.`last_modified` >= ?';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->id, $this->updatedSince);

		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllNewFeed($this->id, $this->updatedSince, 
			$this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFeed(){
		$sql = 'AND `items`.`feed_id` = ? ' .
			'AND `items`.`id` < ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->id, $this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFeed($this->id, $this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFeedOffsetZero(){
		$sql = 'AND `items`.`feed_id` = ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->id);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFeed($this->id, $this->limit, 
				0, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFolder(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
			'AND `items`.`id` < ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->id, 
			$this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFolder($this->id, $this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllFolderOffsetZero(){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->id);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAllFolder($this->id, $this->limit, 
				0, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAll(){
		$sql = 'AND `items`.`id` < ? ' .
			'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user, $this->offset);
		$this->setMapperResult($sql, $params, $this->rows);
		$result = $this->mapper->findAll($this->limit, 
				$this->offset, $this->status, $this->user);

		$this->assertEquals($this->items, $result);
	}


	public function testFindAllOffsetZero(){
		$sql = 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $this->status);
		$params = array($this->user);
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


	public function testDeleteReadOlderThanThresholdDoesNotDeleteBelowThreshold(){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT COUNT(*) `size`, `feed_id` ' .
			'FROM `*PREFIX*news_items` ' .
			'WHERE NOT ((`status` & ?) > 0) ' .
			'GROUP BY `feed_id` ' .
			'HAVING COUNT(*) > ?';

		$threshold = 10;
		$rows = array(array('feed_id' => 30, 'size' => 11));
		$params = array($status, $threshold);

		$this->setMapperResult($sql, $params, $rows);
		$this->mapper->deleteReadOlderThanThreshold($threshold);
	}


	public function testDeleteReadOlderThanThreshold(){
		$threshold = 10;
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;

		$sql1 = 'SELECT COUNT(*) `size`, `feed_id` ' .
			'FROM `*PREFIX*news_items` ' .
			'WHERE NOT ((`status` & ?) > 0) ' .
			'GROUP BY `feed_id` ' .
			'HAVING COUNT(*) > ?';
		$params1 = array($status, $threshold);

		
		$row = array('feed_id' => 30, 'size' => 9);

		$sql2 = 'DELETE FROM `*PREFIX*news_items` `items` ' .
				'WHERE NOT ((`status` & ?) > 0) ' .
				'AND `feed_id` = ? ' .
				'ORDER BY `items`.`id` ASC';
		$params2 = array($status, 30);

		
		$pdoResult = $this->getMock('Result', 
			array('fetchRow'));

		$pdoResult->expects($this->at(0))
			->method('fetchRow')
			->will($this->returnValue($row));
		$pdoResult->expects($this->at(1))
			->method('fetchRow')
			->will($this->returnValue(false));
			
		$query = $this->getMock('Query', 
			array('execute'));
		$query->expects($this->at(0))
			->method('execute')
			->with($this->equalTo($params1))
			->will($this->returnValue($pdoResult));

		$this->api->expects($this->at(0))
			->method('prepareQuery')
			->with($this->equalTo($sql1))
			->will(($this->returnValue($query)));

		$query2 = $this->getMock('Query', 
			array('execute'));
		$query2->expects($this->at(0))
			->method('execute')
			->with($this->equalTo($params2));

		$this->api->expects($this->at(1))
			->method('prepareQuery')
			->with($this->equalTo($sql2), $this->equalTo(1))
			->will($this->returnValue($query2));

		$result = $this->mapper->deleteReadOlderThanThreshold($threshold);
	}


	public function testGetNewestItem() {
		$sql = 'SELECT MAX(`items`.`id`) AS `max_id` FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ?';
		$params = array($this->user);
		$rows = array(array('max_id' => 3));

		$this->setMapperResult($sql, $params, $rows);
		
		$result = $this->mapper->getNewestItemId($this->user);
		$this->assertEquals(3, $result);
	}


	public function testGetNewestItemIdNotFound() {
		$sql = 'SELECT MAX(`items`.`id`) AS `max_id` FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ?';
		$params = array($this->user);
		$rows = array();

		$this->setMapperResult($sql, $params, $rows);
		$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');

		$result = $this->mapper->getNewestItemId($this->user);
	}

}