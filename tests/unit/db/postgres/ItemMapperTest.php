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

namespace OCA\News\Db\Postgres;

use \OCA\News\Db\Item;
use \OCA\News\Db\StatusFlag;


require_once(__DIR__ . "/../../../classloader.php");


class ItemMapperTest extends \OCA\News\Utility\MapperTestUtility {

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

		$this->mapper = new ItemMapper($this->db);

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
				'AND `feeds`.`deleted_at` = 0 ' .
				'AND `feeds`.`user_id` = ? ' .
				$prependTo .
			'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
				'ON `folders`.`id` = `feeds`.`folder_id` ' .
			'WHERE `feeds`.`folder_id` = 0 ' .
				'OR `folders`.`deleted_at` = 0 ' .
			'ORDER BY `items`.`id` DESC';
	}


	public function testDeleteReadOlderThanThresholdDoesNotDeleteBelowThreshold(){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT COUNT(*) - `feeds`.`articles_per_update` AS `size`, ' .
		'`items`.`feed_id` AS `feed_id` ' . 
			'FROM `*PREFIX*news_items` `items` ' .
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
			'WHERE NOT ((`items`.`status` & ?) > 0) ' .
			'GROUP BY `items`.`feed_id`, `feeds`.`articles_per_update` ' .
			'HAVING COUNT(*) > ?';

		$threshold = 10;
		$rows = array(array('feed_id' => 30, 'size' => 9));
		$params = array($status, $threshold);

		$this->setMapperResult($sql, $params, $rows);
		$this->mapper->deleteReadOlderThanThreshold($threshold);


	}


	public function testDeleteReadOlderThanThreshold(){
		$threshold = 10;
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;

		$sql1 = 'SELECT COUNT(*) - `feeds`.`articles_per_update` AS `size`, ' .
		'`items`.`feed_id` AS `feed_id` ' . 
			'FROM `*PREFIX*news_items` `items` ' .
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
			'WHERE NOT ((`items`.`status` & ?) > 0) ' .
			'GROUP BY `items`.`feed_id`, `feeds`.`articles_per_update` ' .
			'HAVING COUNT(*) > ?';
		$params1 = array($status, $threshold);


		$row = array('feed_id' => 30, 'size' => 11);

		$sql2 = 'DELETE FROM `*PREFIX*news_items` ' .
				'WHERE `id` IN (' .
					'SELECT `id` FROM `*PREFIX*news_items` ' .
					'WHERE NOT ((`status` & ?) > 0) ' .
					'AND `feed_id` = ? ' .
					'ORDER BY `id` ASC ' .
					'LIMIT ?' .
				')';
		$params2 = array($status, 30, 1);


		$this->setMapperResult($sql1, $params1, array($row));
		$this->setMapperResult($sql2, $params2);

		$this->mapper->deleteReadOlderThanThreshold($threshold);
	}


}
