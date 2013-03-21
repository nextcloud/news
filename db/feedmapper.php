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


class FeedMapper extends NewsMapper {


	public function __construct(API $api) {
		parent::__construct($api, 'news_feeds');
	}


	public function find($id, $userId){
		$sql = 'SELECT * FROM `*dbprefix*news_feeds` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';

		$row = $this->findRow($sql, $id, $userId);
		$feed = new Feed();
		$feed->fromRow($row);

		return $feed;
	}


	private function findAllRows($sql, $params=array()){
		$result = $this->execute($sql, $params);
		
		$feeds = array();
		while($row = $result->fetchRow()){
			$feed = new Feed();
			$feed->fromRow($row);
			array_push($feeds, $feed);
		}

		return $feeds;
	}


	public function findAllFromUser($userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS unread_count ' .
			'FROM `*dbprefix*news_feeds` `feeds` ' .
			'LEFT OUTER JOIN `*dbprefix*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' . 
			'WHERE (`items`.`status` & ?) > 0 ' .
				'AND `feeds`.`user_id` = ? ' .
			'GROUP BY `items`.`feed_id`';
		$params = array($userId);

		return $this->findAllRows($sql, $params);
	}


	public function findAll(){
		$sql = 'SELECT * FROM `*dbprefix*news_feeds`';

		return $this->findAllRows($sql);
	}


	public function getStarredCount($userId){
		$sql = 'SELECT COUNT(*) AS size FROM `*dbprefix*news_feeds` ' .
			'AND `user_id` = ? ' .
			'AND ((`status` & ?) > 0)';
		$params = array($userId, StatusFlag::STARRED);

		$result = $this->execute($sql, $params)->fetchRow();

		return $result['size'];
	}


}