<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * This class maps an item to a row of the items table in the database.
 * It follows the Data Mapper pattern (see http://martinfowler.com/eaaCatalog/dataMapper.html).
 */
class OC_News_ItemMapper {

	const tableName = '*PREFIX*news_items';

	/**
	 * @brief Retrieve all the item corresponding to a feed from the database
	 * @param feedid The id of the feed in the database table.
	 */
	public function findAll($feedid){
		$stmt = OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE feed_id = ?');
		$result = $stmt->execute(array($feedid));
	
		$items = array();
		while ($row = $result->fetchRow()) {
			$url = $row['url'];
			$title = $row['title'];
			$guid = $row['guid'];
			$status = $row['status'];
			$item = new OC_News_Item($url, $title, $guid);
			$item->setStatus($status);
			$items[] = $item;
		}

		return $items;
	}

	public function findIdFromGuid($guid, $feedid){
		$stmt = OCP\DB::prepare('
				SELECT * FROM ' . self::tableName . ' 
				WHERE guid = ?
				AND feed_id = ?
				');
		$result = $stmt->execute(array($guid, $feedid));
		$row = $result->fetchRow();
		$id = null;
		if ($row != null){
			$id = $row['id'];
		}
		return $id;
	}

	/**
	 * @brief Save the feed and all its items into the database
	 * @returns The id of the feed in the database table.
	 */
	public function save(OC_News_Item $item, $feedid){
		$guid = $item->getGuid();
		$status = $item->getStatus();
		echo $status;
		
		$itemid =  $this->findIdFromGuid($guid, $feedid);
		
		if ($itemid == null){
			$title = $item->getTitle();
			
			$stmt = OCP\DB::prepare('
				INSERT INTO ' . self::tableName .
				'(url, title, guid, feed_id, status)
				VALUES (?, ?, ?, ?, ?)
				');

			if(empty($title)) {
				$l = OC_L10N::get('news');
				$title = $l->t('no title');
			}

			$params=array(
				htmlspecialchars_decode($item->getUrl()),
				htmlspecialchars_decode($title),
				$guid,
				$feedid,
				$status
			);
			
			$stmt->execute($params);
			
			$itemid = OCP\DB::insertid(self::tableName);
		}
		// update item: its status might have changed
		// TODO: maybe make this a new function
		else {
			$stmt = OCP\DB::prepare('
				UPDATE ' . self::tableName .
				' SET status = ?
				WHERE id = ?
				');
			
			$params=array(
				$status,
				$itemid
			);
			$stmt->execute($params);
		}
		$item->setId($itemid);
		return $itemid;
	}

	/**
	 * @brief Retrieve an item from the database
	 * @param id The id of the feed in the database table.
	 */
	public function find($id){
		$stmt = OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		$row = $result->fetchRow();

		$url = $row['url'];
		$title = $row['title'];

	}

	
	/**
	 * @brief Permanently delete all items belonging to a feed from the database
	 * @param feedid The id of the feed that we wish to delete
	 * @return 
	 */
	public function deleteAll($feedid){
		
		$stmt = OCP\DB::prepare("
			DELETE FROM " . self::tableName . 
			"WHERE feedid = $id
			");

		$result = $stmt->execute();
		
		return $result;
	}
}