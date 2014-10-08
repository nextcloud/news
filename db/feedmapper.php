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

use \OCP\IDb;
use \OCP\AppFramework\Db\Entity;


class FeedMapper extends NewsMapper {


	public function __construct(IDb $db) {
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
		$params = [$id, $userId];

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
		$params = [$userId];

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
				// because prepared statements don't work. This is a possible
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
		$params = [$hash, $userId];

		return $this->findEntity($sql, $params);
	}


	public function delete(Entity $entity){
		parent::delete($entity);

		// someone please slap me for doing this manually :P
		// we needz CASCADE + FKs please
		$sql = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` = ?';
		$params = [$entity->getId()];
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
		$params = [];

		// sometimes we want to delete all entries
		if ($deleteOlderThan !== null) {
			$sql .= 'AND `deleted_at` < ? ';
			$params[] = $deleteOlderThan;
		}

		// we need to sometimes only delete feeds of a user
		if($userId !== null) {
			$sql .= 'AND `user_id` = ?';
			$params[] = $userId;
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
		$this->execute($sql, [$userId]);
	}


}
