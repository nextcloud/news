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

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Db\Entity;


class FeedMapper extends Mapper implements IMapper {


	public function __construct(API $api) {
		parent::__construct($api, 'news_feeds');
	}


	public function find($id, $userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' . 
				'AND (`items`.`status` & ?) = ? ' .
			'WHERE `feeds`.`id` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
			'GROUP BY `feeds`.`id`';
		$params = array(StatusFlag::UNREAD, StatusFlag::UNREAD, $id, $userId);

		$row = $this->findOneQuery($sql, $params);
		$feed = new Feed();
		$feed->fromRow($row);

		return $feed;
	}


	private function findAllRows($sql, $params=array(), $limit=null){
		$result = $this->execute($sql, $params, $limit);
		
		$feeds = array();
		while($row = $result->fetchRow()){
			$feed = new Feed();
			$feed->fromRow($row);
			array_push($feeds, $feed);
		}

		return $feeds;
	}


	public function findAllFromUser($userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' . 
				'AND (`items`.`status` & ?) = ? ' .
			'WHERE `feeds`.`user_id` = ? ' .
			'GROUP BY `feeds`.`id`';
		$params = array(StatusFlag::UNREAD, StatusFlag::UNREAD, $userId);

		return $this->findAllRows($sql, $params);
	}


	public function findAll(){
		$sql = 'SELECT * FROM `*PREFIX*news_feeds`';

		return $this->findAllRows($sql);
	}


	public function findByUrlHash($hash, $userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' . 
				'AND (`items`.`status` & ?) = ? ' .
			'WHERE `feeds`.`url_hash` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
			'GROUP BY `feeds`.`id`';
		$params = array(StatusFlag::UNREAD, StatusFlag::UNREAD, $hash, $userId);

		$row = $this->findOneQuery($sql, $params);
		$feed = new Feed();
		$feed->fromRow($row);

		return $feed;
	}


	public function delete(Entity $entity){
		parent::delete($entity);

		// someone please slap me for doing this manually :P
		// we needz CASCADE + FKs please
		$sql = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` = ?';
		$params = array($entity->getId());
		$this->execute($sql, $params);
	}



}