<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

/**
 * This class maps an item to a row of the items table in the database.
 * It follows the Data Mapper pattern (see http://martinfowler.com/eaaCatalog/dataMapper.html).
 */
class ItemMapper {

	const tableName = '*PREFIX*news_items';
	private $userid;

	public function __construct($userid = null) {
		if ($userid !== null) {
			$this->userid = $userid;
		}
		else {
			$this->userid = \OCP\USER::getUser();
		}
	}

	/**
	 * @brief
	 * @param row a row from the items table of the database
	 * @returns an object of the class OC_News_Item
	 */
	public function fromRow($row) {
		$url = $row['url'];
		$title = $row['title'];
		$guid = $row['guid'];
		$body = $row['body'];
		$id = $row['id'];
		$item = new Item($url, $title, $guid, $body, $id);
		$item->setStatus($row['status']);
		$item->setAuthor($row['author']);
		$item->setFeedId($row['feed_id']);
		$item->setDate(Utils::dbtimestampToUnixtime($row['pub_date']));

		return $item;
	}

	/**
	 * @brief Retrieve all the item corresponding to a feed from the database
	 * @param feedid The id of the feed in the database table.
	 */
	public function findByFeedId($feedid) {
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE feed_id = ? ORDER BY pub_date DESC');
		$result = $stmt->execute(array($feedid));

		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}


	/**
	 * @brief Retrieve all the items corresponding to a feed from the database with a particular status
	 * @param feedid The id of the feed in the database table.
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function findAllStatus($feedid, $status) {
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . '
				WHERE feed_id = ?
				AND ((status & ?) > 0)
				ORDER BY pub_date DESC');
		$result = $stmt->execute(array($feedid, $status));

		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	/*
	 * @brief Retrieve all the items from the database with a particular status
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function findEveryItemByStatus($status) {
		$stmt = \OCP\DB::prepare('SELECT ' . self::tableName . '.* FROM ' . self::tableName . '
				JOIN '. FeedMapper::tableName .' ON
				'. FeedMapper::tableName .'.id = ' . self::tableName . '.feed_id
				WHERE '. FeedMapper::tableName .'.user_id = ?
				AND ((' . self::tableName . '.status & ?) > 0)
				ORDER BY ' . self::tableName . '.pub_date DESC');
		$result = $stmt->execute(array($this->userid, $status));

		$items = array();
		while ($row = $result->fetchRow()) {
			$item = $this->fromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	public function countAllStatus($feedid, $status) {
		$stmt = \OCP\DB::prepare('SELECT COUNT(*) as size FROM ' . self::tableName . '
				WHERE feed_id = ?
				AND ((status & ?) > 0)');
		$result=$stmt->execute(array($feedid, $status))->fetchRow();
		return $result['size'];
	}

	/**
	 * @brief Count all the items from the database with a particular status
	 * @param status one of the constants defined in OCA\News\StatusFlag
	 */
	public function countEveryItemByStatus($status) {
		$stmt = \OCP\DB::prepare('SELECT COUNT(*) as size FROM ' . self::tableName . '
				JOIN '. FeedMapper::tableName .' ON
				'. FeedMapper::tableName .'.id = ' . self::tableName . '.feed_id
				WHERE '. FeedMapper::tableName .'.user_id = ?
				AND ((' . self::tableName . '.status & ?) > 0)');
		$result = $stmt->execute(array($this->userid, $status))->fetchRow();;

		return $result['size'];
	}

	public function findIdFromGuid($guid_hash, $guid, $feedid) {
		$stmt = \OCP\DB::prepare('
				SELECT * FROM ' . self::tableName . '
				WHERE guid_hash = ?
				AND feed_id = ?
				');
		$result = $stmt->execute(array($guid_hash, $feedid));
		//TODO: if there is more than one row, falling back to comparing $guid
		$row = $result->fetchRow();
		$id = null;
		if ($row != null) {
			$id = $row['id'];
		}
		return $id;
	}

	/**
	 * @brief Update the item after its status has changed
	 * @returns The item whose status has changed.
	 */
	public function update(Item $item) {

		$itemid = $item->getId();
		$status = $item->getStatus();

		$stmt = \OCP\DB::prepare('
				UPDATE ' . self::tableName .
				' SET status = ?
				WHERE id = ?
				');

		$params=array(
			$status,
			$itemid
			);
		$stmt->execute($params);

		return true;
	}

	/**
	 * @brief Save the feed and all its items into the database
	 * @returns The id of the feed in the database table.
	 */
	public function save(Item $item, $feedid) {
		$guid = $item->getGuid();
		$guid_hash = md5($guid);

		$status = $item->getStatus();

		$itemid =  $this->findIdFromGuid($guid_hash, $guid, $feedid);

		if ($itemid == null) {
			$title = $item->getTitle();
			$body = $item->getBody();
			$author = $item->getAuthor();

			$stmt = \OCP\DB::prepare('
				INSERT INTO ' . self::tableName .
				'(url, title, body, author, guid, guid_hash, pub_date, feed_id, status)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
				');

			if(empty($title)) {
				$l = \OC_L10N::get('news');
				$title = $l->t('no title');
			}

			if(empty($body)) {
				$l = \OC_L10N::get('news');
				$body = $l->t('no body');
			}

			$pub_date = Utils::unixtimeToDbtimestamp($item->getDate());

			$params=array(
				$item->getUrl(),
				$title,
				$body,
				$author,
				$guid,
				$guid_hash,
				$pub_date,
				$feedid,
				$status
			);

			$stmt->execute($params);

			$itemid = \OCP\DB::insertid(self::tableName);
		}
		else {
			$this->update($item);
		}
		$item->setId($itemid);
		return $itemid;
	}

	/**
	 * @brief Retrieve an item from the database
	 * @param id The id of the feed in the database table.
	 */
	public function findById($id) {
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		$row = $result->fetchRow();

		$item = $this->fromRow($row);

		return $item;

	}


	/**
	 * @brief Permanently delete all items belonging to a feed from the database
	 * @param feedid The id of the feed that we wish to delete
	 * @return
	 */
	public function deleteAll($feedid) {
		if ($feedid == null) {
			return false;
		}
		$stmt = \OCP\DB::prepare('DELETE FROM ' . self::tableName .' WHERE feed_id = ?');

		$result = $stmt->execute(array($feedid));

		return $result;
	}
}