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
		return 'SELECT `*PREFIX*news_items`.* FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `*PREFIX*news_feeds`.`id` = `*PREFIX*news_items`.`feed_id` '.
				'AND `*PREFIX*news_feeds`.`user_id` = ? ' . $prependTo;
	}

	private function makeSelectQueryStatus($prependTo) {
		return $this->makeSelectQuery(
			'AND ((`*PREFIX*news_items`.`status` & ?) > 0) ' .
			$prependTo
		);
	}
	

	public function find($id, $userId){
		$sql = $this->makeSelectQuery('WHERE `*PREFIX*news_items`.`id` = ? ');
		$row = $this->findOneQuery($sql, array($id, $userId));
		
		$item = new Item();
		$item->fromRow($row);
		
		return $item;
	}


	public function starredCount($userId){
		$sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
			'WHERE ((`items`.`status` & ?) > 0)';

		$params = array($userId, StatusFlag::STARRED);

		$result = $this->execute($sql, $params)->fetchRow();

		return $result['size'];
	}


	public function readFeed($feedId, $userId){
		$sql = 'UPDATE `*PREFIX*news_feeds` `feeds` ' .
			'JOIN `*PREFIX*news_items` `items` ' .
				'ON `items`.`feed_id` = `feeds`.`id` ' .
				'AND `feeds`.`user_id` = ? ' .
			'SET `items`.`status` = (`items`.`status` & ?) ' .
			'WHERE `items`.`id` = ?';
		$params = array(~StatusFlag::UNREAD, $userId, $feedId);

		$this->execute($sql, $params);
	}


	public function findAllNewFeed($id, $updatedSince, $status, $userId){
		$sql = 'AND `items`.`feed_id` = ? ' .
				'AND `items`.`lastmodified` >= ?';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($userId, $status, $id, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllNewFolder($id, $updatedSince, $status, $userId){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
				'AND `items`.`lastmodified` >= ?';
		$sql = $this->makeSelectQueryStatus($sql);
		$params = array($userId, $status, $id, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllNew($updatedSince, $status, $userId){
		$sql = $this->makeSelectQueryStatus('AND `items`.`lastmodified` >= ?');
		$params = array($userId, $status, $updatedSince);
		return $this->findAllRows($sql, $params);
	}


	public function findAllFeed($id, $limit, $offset, $status, $userId){
		$params = array($userId, $status, $id);
		$sql = 'AND `items`.`feed_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		return $this->findAllRows($sql, $params, $limit);
	}


	public function findAllFolder($id, $limit, $offset, $status, $userId){
		$params = array($userId, $status, $id);
		$sql = 'AND `feeds`.`folder_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		return $this->findAllRows($sql, $params, $limit);
	}


	public function findAll($limit, $offset, $status, $userId){
		$params = array($userId, $status);
		$sql = '';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` > ? ';
			array_push($params, $offset);
		}
		$sql .= 'ORDER BY `items`.`id` DESC ';
		$sql = $this->makeSelectQueryStatus($sql);
		return $this->findAllRows($sql, $params, $limit);
	}

}
