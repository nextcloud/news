<?php
/**
* ownCloud - News app
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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;

class ItemMapper extends Mapper implements IMapper {

	public function __construct(API $api){
		parent::__construct($api, 'news_items');
	}
	

	protected function findAllRows($sql, $params, $limit=null, $offset=null) {
		$result = $this->execute($sql, $params, $limit, $offset);

		$items = array();

		while($row = $result->fetchRow()){
			$item = new Item();
			$item->fromRow($row);
			
			array_push($items, $item);
		}

		return $items;
	}
	

	private function makeSelectQuery($prependTo){
		return 'SELECT `items`.* FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ? ' . $prependTo;
	}

	private function makeSelectQueryStatus($prependTo, $status) {
		// Hi this is Ray and you're watching Jack Ass
		// Now look closely: this is how we adults handle weird bugs in our
		// code: we take them variables and we cast the shit out of them
		$status = (int) $status;

		// prepare for the unexpected
		if(!is_numeric($status)) {
			die(); die(); die('If you can read this something is terribly wrong');
		}

		// now im gonna slowly stick them in the query, be careful!
		return $this->makeSelectQuery(

			// WARNING: this is a desperate attempt at making this query work
			// because prepared statements dont work. This is a possible 
			// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
			// think twice when changing this
			'AND ((`items`.`status` & ' . $status . ') = ' . $status . ') ' .
			$prependTo
		);
	}
	

	public function find($id, $userId){
		$sql = $this->makeSelectQuery('AND `items`.`id` = ? ');
		$row = $this->findOneQuery($sql, array($userId, $id));
		
		$item = new Item();
		$item->fromRow($row);
		
		return $item;
	}


	public function starredCount($userId){
		$sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
			// WARNING: this is a desperate attempt at making this query work
			// because prepared statements dont work. This is a possible 
			// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
			// think twice when changing this
			'WHERE ((`items`.`status` & ' . StatusFlag::STARRED . ') = ' . 
				StatusFlag::STARRED . ')';

		$params = array($userId);

		$result = $this->execute($sql, $params)->fetchRow();

		return $result['size'];
	}


	public function readFeed($feedId, $highestItemId, $userId){
		$sql = 'UPDATE `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
				'AND `feeds`.`id` = ? ' .
				'AND `items`.`id` <= ? ' .
			'SET `items`.`status` = (`items`.`status` & ?) ';
		$params = array($userId, $feedId, $highestItemId, ~StatusFlag::UNREAD);

		$this->execute($sql, $params);
	}


	public function findAllNewFeed($id, $updatedSince, $status, $userId){
		$sql = 'AND `items`.`feed_id` = ? ' .
				'AND `items`.`id` >= ?';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		$params = array($userId, $id, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllNewFolder($id, $updatedSince, $status, $userId){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
				'AND `items`.`id` >= ?';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		$params = array($userId, $id, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllNew($updatedSince, $status, $userId){
		$sql = $this->makeSelectQueryStatus('AND `items`.`id` >= ?', $status);
		$params = array($userId, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllFeed($id, $limit, $offset, $status, $userId){
		$params = array($userId, $id);
		$sql = 'AND `items`.`feed_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findAllRows($sql, $params, $limit);
	}


	public function findAllFolder($id, $limit, $offset, $status, $userId){
		$params = array($userId, $id);
		$sql = 'AND `feeds`.`folder_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findAllRows($sql, $params, $limit);
	}


	public function findAll($limit, $offset, $status, $userId){
		$params = array($userId);
		$sql = '';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findAllRows($sql, $params, $limit);
	}


	public function findByGuidHash($guidHash, $feedId, $userId){
		$sql = $this->makeSelectQuery(
			'AND `items`.`guid_hash` = ? ' .
			'AND `feeds`.`id` = ? ');
		$row = $this->findOneQuery($sql, array($userId, $guidHash, $feedId));
		
		$item = new Item();
		$item->fromRow($row);
		
		return $item;
	}


	public function getReadOlderThanThreshold($threshold){

		// we want items that are not starred and not unread
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT * FROM `*PREFIX*news_items` ' .
			'WHERE NOT ((`status` & ?) > 0)';

		$params = array($status);
		return $this->findAllRows($sql, $params, $threshold);
	}


	public function deleteReadOlderThanId($id){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'DELETE FROM `*PREFIX*news_items` WHERE `id` < ? ' .
			'AND NOT ((`status` & ?) > 0)';
		$params = array($id, $status);
		$this->execute($sql, $params);
	}


}
