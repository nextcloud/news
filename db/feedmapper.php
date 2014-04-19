<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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

use \OCA\News\Core\Db;


class FeedMapper extends Mapper implements IMapper {


	public function __construct(Db $db) {
		parent::__construct($db, 'news_feeds', '\OCA\News\Db\Feed');
	}


	public function find($id, $userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				// WARNING: this is a desperate attempt at making this query work
				// because prepared statements dont work. This is a possible
				// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
				// think twice when changing this
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`id` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
			'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
				'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
				'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
				'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$params = array($id, $userId);

		return $this->findEntity($sql, $params);
	}


	public function findAllFromUser($userId){
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
		$params = array($userId);

		return $this->findEntities($sql, $params);
	}


	public function findAll(){
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

		return $this->findEntities($sql);
	}


	public function findByUrlHash($hash, $userId){
		$sql = 'SELECT `feeds`.*, COUNT(`items`.`id`) AS `unread_count` ' .
			'FROM `*PREFIX*news_feeds` `feeds` ' .
			'LEFT JOIN `*PREFIX*news_items` `items` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
				// WARNING: this is a desperate attempt at making this query work
				// because prepared statements dont work. This is a possible
				// SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
				// think twice when changing this
				'AND (`items`.`status` & ' . StatusFlag::UNREAD . ') = ' .
				StatusFlag::UNREAD . ' ' .
			'WHERE `feeds`.`url_hash` = ? ' .
				'AND `feeds`.`user_id` = ? ' .
			'GROUP BY `feeds`.`id`, `feeds`.`user_id`, `feeds`.`url_hash`,'.
				'`feeds`.`url`, `feeds`.`title`, `feeds`.`link`,'.
				'`feeds`.`favicon_link`, `feeds`.`added`, `feeds`.`articles_per_update`,'.
				'`feeds`.`folder_id`, `feeds`.`prevent_update`, `feeds`.`deleted_at`';
		$params = array($hash, $userId);

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


	/**
	 * @param int $deleteOlderThan if given gets all entries with a delete date
	 * older than that timestamp
	 * @param string $userId if given returns only entries from the given user
	 * @return array with the database rows
	 */
	public function getToDelete($deleteOlderThan=null, $userId=null) {
		$sql = 'SELECT * FROM `*PREFIX*news_feeds` ' .
			'WHERE `deleted_at` > 0 ';
		$params = array();

		// sometimes we want to delete all entries
		if ($deleteOlderThan !== null) {
			$sql .= 'AND `deleted_at` < ? ';
			array_push($params, $deleteOlderThan);
		}

		// we need to sometimes only delete feeds of a user
		if($userId !== null) {
			$sql .= 'AND `user_id` = ?';
			array_push($params, $userId);
		}

		return $this->findEntities($sql, $params);
	}


	/**
	 * Deletes all feeds of a user, delete items first since the user_id
	 * is not defined in there
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `user_id` = ?';
		$this->execute($sql, array($userId));
	}


}
