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

/**
 * This class maps a feed to an entry in the feeds table of the database.
 */
class OC_News_FolderMapper {

	const tableName = '*PREFIX*news_folders';

	private $userid;

	public function __construct($userid = null){
		if ($userid !== null) {
			$this->userid = $userid;
		}
		$this->userid = OCP\USER::getUser();
	}

	/**
	 * @brief Create a folder and populate with children from the database
	 * @param folder The folder to be populated.
	 * @returns  an instance of OC_News_Folder
	 */
	public function populate($folder){
		// first add child feeds
		$feedmapper = new OC_News_FeedMapper();
		$feeds = $feedmapper->findByFolderId($folder->getId());
		foreach ($feeds as $feed){
			$folder->addChild($feed);
		}

		// and second child folders
		$stmt = OCP\DB::prepare('SELECT *
					FROM ' . self::tableName .
					' WHERE user_id = ? AND parent_id = ?');
		$result = $stmt->execute(array($this->userid, $folder->getId()));

		while( $row = $result->fetchRow()){
			$unpopfolder = new OC_News_Folder($row['name'], $row['id']);
			$popfolder = self::populate($unpopfolder);
			$folder->addChild($popfolder);
		}

		return $folder;
	}

	/**
	 * @brief Retrieve a folder from the database
	 * @param id The id of the folder in the database table.
	 * @returns  an instance of OC_News_Folder
	 */
	public function find($id){
		$stmt = OCP\DB::prepare('SELECT *
					FROM ' . self::tableName .
					' WHERE user_id = ? AND id = ?');
		$result = $stmt->execute(array($this->userid, $id));

		$row = $result->fetchRow();
		$folder = new OC_News_Folder($row['name'], $row['id']);

		return $folder;
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
	public function save(OC_News_Folder $folder){
		$query = OCP\DB::prepare('
			INSERT INTO ' . self::tableName .
			'(name, parent_id, user_id)
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
		$this->userid
		);
		$query->execute($params);
		$folderid = OCP\DB::insertid(self::tableName);

		$folder->setId($folderid);
		return $folderid;
	}

	public function delete(OC_News_Folder $folder){
		$folderid = $folder->getId();
		return deleteById(folderid);
	}

	//TODO: replace it with a DELETE INNER JOIN operation
	//Note Brumm: probably not possible, I've tried:
	// 	'DELETE FROM ' . self::tableName .' INNER JOIN ' . OC_News_FeedMapper::tableName .
	// 			' ON (' . self::tableName . '.id = ' . OC_News_FeedMapper::tableName . '.folder_id )')
	public function deleteById($folderid){
		if ($folderid == null){
			return false;
		}

		$stmt = OCP\DB::prepare('DELETE FROM ' . self::tableName .' WHERE id = ?');

		$result = $stmt->execute(array($folderid));

		$feedMapper = new OC_News_FeedMapper();
		//TODO: handle the value that the execute returns
		$feedMapper->deleteAll($folderid);

		return true;
	}

}