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


class FeedMapperTest extends \OCP\AppFramework\Db\MapperTestUtility {

	private $mapper;
	private $feeds;

	protected function setUp(){
		parent::setUp();

		$this->mapper = new FeedMapper($this->db);

		// create mock feeds
		$feed1 = new Feed();
		$feed2 = new Feed();

		$this->feeds = array(
			$feed1,
			$feed2
		);
		$this->user = 'herman';
	}


	public function testFind(){
		$userId = 'john';
		$id = 3;
		$rows = array(
		  array('id' => $this->feeds[0]->getId()),
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`id` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$params = array($id, $userId);
		$this->setMapperResult($sql, $params, $rows);

		$result = $this->mapper->find($id, $userId);
		$this->assertEquals($this->feeds[0], $result);

	}


	public function testFindNotFound(){
		$userId = 'john';
		$id = 3;
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`id` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$params = array($id, $userId);
		$this->setMapperResult($sql, $params);

		$this->setExpectedException('\OCP\AppFramework\Db\DoesNotExistException');
		$this->mapper->find($id, $userId);
	}


	public function testFindMoreThanOneResultFound(){
		$userId = 'john';
		$id = 3;
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`id` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$params = array($id, $userId);
		$this->setMapperResult($sql, $params, $rows);

		$this->setExpectedException('\OCP\AppFramework\Db\MultipleObjectsReturnedException');
		$this->mapper->find($id, $userId);
	}


	public function testFindAll(){
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` '.
				'ON `feeds`.`folder_id` = `folders`.`id` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				// WARNING: this is a desperate attempt at making this query work
				// because prepared statements dont work. This is a possible
				// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
				// think twice when changing this
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE (`feeds`.`folder_id` = 0 ' .
				'OR `folders`.`deleted_at` = 0' .
			')' .
			'AND `feeds`.`deleted_at` = 0 ' .
			'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
				'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
				'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
				'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';

		$this->setMapperResult($sql, array(), $rows);

		$result = $this->mapper->findAll();
		$this->assertEquals($this->feeds, $result);
	}


	public function testFindAllFromUser(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` '.
				'ON `feeds`.`folder_id` = `folders`.`id` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				// WARNING: this is a desperate attempt at making this query work
				// because prepared statements dont work. This is a possible
				// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
				// think twice when changing this
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`user_id` = ? ' .
			'AND (`feeds`.`folder_id` = 0 ' .
				'OR `folders`.`deleted_at` = 0' .
			')' .
			'AND `feeds`.`deleted_at` = 0 ' .
			'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
				'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
				'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
				'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$this->setMapperResult($sql,
			array($userId), $rows);

		$result = $this->mapper->findAllFromUser($userId);
		$this->assertEquals($this->feeds, $result);
	}


	public function testFindByUrlHash(){
		$urlHash = md5('hihi');
		$row = array(
			array('id' => $this->feeds[0]->getId()),
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`url_hash` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$this->setMapperResult($sql,
			array($urlHash, $this->user), $row);

		$result = $this->mapper->findByUrlHash($urlHash, $this->user);
		$this->assertEquals($this->feeds[0], $result);
	}


	public function testFindByUrlHashNotFound(){
		$urlHash = md5('hihi');
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`url_hash` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$this->setMapperResult($sql,
			array($urlHash, $this->user));

		$this->setExpectedException('\OCP\AppFramework\Db\DoesNotExistException');
		$this->mapper->findByUrlHash($urlHash, $this->user);
	}


	public function testFindByUrlHashMoreThanOneResultFound(){
		$urlHash = md5('hihi');
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`url_hash` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
		      	'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
		        	'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
		        	'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
		        	'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$this->setMapperResult($sql,
			array($urlHash, $this->user), $rows);

		$this->setExpectedException('\OCP\AppFramework\Db\MultipleObjectsReturnedException');
		$this->mapper->findByUrlHash($urlHash, $this->user);
	}


	public function testDelete(){
		$feed = new Feed();
		$feed->setId(3);

		$sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `id` = ?';
		$arguments = array($feed->getId());

		$sql2 = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` = ?';
		$arguments2 = array($feed->getId());

		$pdoResult = $this->getMock('Result',
			array('fetchRow'));
		$pdoResult->expects($this->any())
			->method('fetchRow');

		$this->setMapperResult($sql, $arguments);
		$this->setMapperResult($sql2, $arguments2);

		$this->mapper->delete($feed);

	}


	public function testGetPurgeDeleted(){
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_feeds` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ';
		$this->setMapperResult($sql, array($deleteOlderThan), $rows);
		$result = $this->mapper->getToDelete($deleteOlderThan);

		$this->assertEquals($this->feeds, $result);
	}


	public function testGetPurgeDeletedFromUser(){
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_feeds` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($deleteOlderThan, $this->user), $rows);
		$result = $this->mapper->getToDelete($deleteOlderThan, $this->user);

		$this->assertEquals($this->feeds, $result);
	}


	public function testGetAllPurgeDeletedFromUser(){
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);

		$sql = 'SELECT * FROM `*PREFIX*news_feeds` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($this->user), $rows);
		$result = $this->mapper->getToDelete(null, $this->user);

		$this->assertEquals($this->feeds, $result);
	}


	public function testDeleteFromUser(){
		$userId = 'john';
		$sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `user_id` = ?';

		$this->setMapperResult($sql, array($userId));

		$this->mapper->deleteUser($userId);
	}


}
