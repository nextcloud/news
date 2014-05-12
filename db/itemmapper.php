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

use \OCA\News\Core\Db;

class ItemMapper extends Mapper implements IMapper {

	public function __construct(Db $db){
		parent::__construct($db, 'news_items', '\OCA\News\Db\Item');
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

	private function makeSelectQueryStatus($prependTo, $status) {
		// Hi this is Ray and you're watching Jack Ass
		// Now look closely: this is how we adults handle weird bugs in our
		// code: we take them variables and we cast the shit out of them
		$status = (int) $status;

		// prepare for the unexpected
		if(!is_numeric($status)) {
			die('If you can read this something is terribly wrong');
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
		return $this->findEntity($sql, array($userId, $id));
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

		$result = $this->execute($sql, $params)->fetch();

		return (int) $result['size'];
	}


	public function readAll($highestItemId, $time, $userId) {
		$sql = 'UPDATE `*PREFIX*news_items` ' .
			'SET `status` = `status` & ? ' .
			', `last_modified` = ? ' .
			'WHERE `feed_id` IN (' .
				'SELECT `id` FROM `*PREFIX*news_feeds` ' .
					'WHERE `user_id` = ? ' .
				') '.
			'AND `id` <= ?';
		$params = array(~StatusFlag::UNREAD, $time, $userId, $highestItemId);
		$this->execute($sql, $params);
	}


	public function readFolder($folderId, $highestItemId, $time, $userId) {
		$sql = 'UPDATE `*PREFIX*news_items` ' .
			'SET `status` = `status` & ? ' .
			', `last_modified` = ? ' .
			'WHERE `feed_id` IN (' .
				'SELECT `id` FROM `*PREFIX*news_feeds` ' .
					'WHERE `folder_id` = ? ' .
					'AND `user_id` = ? ' .
				') '.
			'AND `id` <= ?';
		$params = array(~StatusFlag::UNREAD, $time, $folderId, $userId,
			$highestItemId);
		$this->execute($sql, $params);
	}


	public function readFeed($feedId, $highestItemId, $time, $userId){
		$sql = 'UPDATE `*PREFIX*news_items` ' .
			'SET `status` = `status` & ? ' .
			', `last_modified` = ? ' .
				'WHERE `feed_id` = ? ' .
				'AND `id` <= ? ' .
				'AND EXISTS (' .
					'SELECT * FROM `*PREFIX*news_feeds` ' .
					'WHERE `user_id` = ? ' .
					'AND `id` = ? ) ';
		$params = array(~StatusFlag::UNREAD, $time, $feedId, $highestItemId,
			$userId, $feedId);

		$this->execute($sql, $params);
	}


	public function findAllNew($updatedSince, $status, $userId){
		$sql = $this->makeSelectQueryStatus(
			'AND `items`.`last_modified` >= ? ', $status);
		$params = array($userId, $updatedSince);
		return $this->findEntities($sql, $params);
	}


	public function findAllNewFolder($id, $updatedSince, $status, $userId){
		$sql = 'AND `feeds`.`folder_id` = ? ' .
				'AND `items`.`last_modified` >= ? ';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		$params = array($userId, $id, $updatedSince);
		return $this->findEntities($sql, $params);
	}


	public function findAllNewFeed($id, $updatedSince, $status, $userId){
		$sql = 'AND `items`.`feed_id` = ? ' .
				'AND `items`.`last_modified` >= ? ';
		$sql = $this->makeSelectQueryStatus($sql, $status);
		$params = array($userId, $id, $updatedSince);
		return $this->findEntities($sql, $params);
	}


	public function findAllFeed($id, $limit, $offset, $status, $userId){
		$params = array($userId, $id);
		$sql = 'AND `items`.`feed_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` < ? ';
			array_push($params, $offset);
		}
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findEntities($sql, $params, $limit);
	}


	public function findAllFolder($id, $limit, $offset, $status, $userId){
		$params = array($userId, $id);
		$sql = 'AND `feeds`.`folder_id` = ? ';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` < ? ';
			array_push($params, $offset);
		}
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findEntities($sql, $params, $limit);
	}


	public function findAll($limit, $offset, $status, $userId){
		$params = array($userId);
		$sql = '';
		if($offset !== 0){
			$sql .= 'AND `items`.`id` < ? ';
			array_push($params, $offset);
		}
		$sql = $this->makeSelectQueryStatus($sql, $status);
		return $this->findEntities($sql, $params, $limit);
	}


	public function findAllUnreadOrStarred($userId) {
		$params = array($userId);
		$status = StatusFlag::UNREAD | StatusFlag::STARRED;
		$sql = 'AND ((`items`.`status` & ' . $status . ') > 0) ';
		$sql = $this->makeSelectQuery($sql);
		return $this->findEntities($sql, $params);
	}


	public function findByGuidHash($guidHash, $feedId, $userId){
		$sql = $this->makeSelectQuery(
			'AND `items`.`guid_hash` = ? ' .
			'AND `feeds`.`id` = ? ');

		return $this->findEntity($sql, array($userId, $guidHash, $feedId));
	}


	/**
	 * Delete all items for feeds that have over $threshold unread and not
	 * starred items
	 */
	public function deleteReadOlderThanThreshold($threshold){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT COUNT(*) - `feeds`.`articles_per_update` AS `size`, ' .
		'`items`.`feed_id` AS `feed_id` ' . 
			'FROM `*PREFIX*news_items` `items` ' .
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
			'WHERE NOT ((`items`.`status` & ?) > 0) ' .
			'GROUP BY `items`.`feed_id`, `feeds`.`articles_per_update` ' .
			'HAVING COUNT(*) > ?';
		$params = array($status, $threshold);
		$result = $this->execute($sql, $params);

		while($row = $result->fetch()) {

			$size = (int) $row['size'];
			$limit = $size - $threshold;

			if($limit > 0) {
				$params = array($status, $row['feed_id']);

				$sql = 'DELETE FROM `*PREFIX*news_items` ' .
				'WHERE NOT ((`status` & ?) > 0) ' .
				'AND `feed_id` = ? ' .
				'ORDER BY `id` ASC';

				$this->execute($sql, $params, $limit);
			}
		}
	}


	public function getNewestItemId($userId) {
		$sql = 'SELECT MAX(`items`.`id`) AS `max_id` FROM `*PREFIX*news_items` `items` '.
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` '.
				'AND `feeds`.`user_id` = ?';
		$params = array($userId);

		$result = $this->findOneQuery($sql, $params);

		return (int) $result['max_id'];
	}


	/**
	 * Deletes all items of a user
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$sql = 'DELETE FROM `*PREFIX*news_items` ' . 
			'WHERE `feed_id` IN (' .
				'SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds` ' .
					'WHERE `feeds`.`user_id` = ?' .
				')';

		$this->execute($sql, array($userId));
	}


}
