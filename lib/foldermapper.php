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
 * This class maps a feed to an entry in the feeds table of the database.
 */
class OC_News_FolderMapper {

	const tableName = '*PREFIX*news_folders';

	/**
	 * @brief Retrieve a feed from the database
	 * @param id The id of the feed in the database table.
	 * @returns  
	 */
	public function find($id){
		$stmt = OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		$row = $result->fetchRow();
		$url = $row['url'];
		$title = $row['title'];
		$feed = new OC_News_Feed($url, $title, null, $id);
		return $feed;
	}

	/**
	 * @brief Retrieve a feed and all its items from the database
	 * @param id The id of the feed in the database table.
	 * @returns 
	 */
	public function findWithItems($id){
		$stmt = OCP\DB::prepare('SELECT * FROM ' . self::tableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		$row = $result->fetchRow();
		$url = $row['url'];
		$title = $row['title'];
		$feed = new OC_News_Feed($url, $title, null,$id);

		$itemMapper = new OC_News_ItemMapper($feed);
		$items = $itemMapper->findAll();
		$feed->setItems($items);
		
		return $feed;
	}

	/**
	 * @brief Store the folder and all its feeds into the database
	 * @param folder the folder to be saved
	 * @returns The id of the folder in the database table.
	 */
	public function insert(OC_News_Folder $folder){
		$query = OCP\DB::prepare('
			INSERT INTO ' . self::tableName .
			'(name, parentid, userid)
			VALUES (?, ?, ?)
			');
		
		$name = $folder->getName();

		if(empty($name)) {
			$l = OC_L10N::get('news');
			$name = $l->t('no name');
		}

		$parentid = $folder->getParentId();

		$params=array(
		htmlspecialchars_decode($name),
		$parentid,
		OCP\USER::getUser()
		);
		$query->execute($params);
		$folderid = OCP\DB::insertid(self::tableName);

		$folder->setId($folderid);

//		$folder->getFeeds();
// 		$feedMapper = new OC_News_FeedMapper($feed);
// 		$items = $feed->getItems();
// 		foreach($items as $item){
// 			$itemMapper->insert($item);
// 		}
//		return $folderid;
	}
}