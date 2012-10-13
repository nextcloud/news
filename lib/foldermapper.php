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
 * This class maps a feed to an entry in the feeds table of the database.
 */
class FolderMapper {

	const tableName = '*PREFIX*news_folders';

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
	 * @brief Returns the forest (list of trees) of folders children of $parentid
	 * @param 
	 * @returns 
	 */
	public function childrenOf($parentid) {
		$folderlist = array(); 
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName .
					' WHERE user_id = ? AND parent_id = ?');
		$result = $stmt->execute(array($this->userid, $parentid));
		
		while( $row = $result->fetchRow()) {
			$folderid = $row['id'];
			$folder = new Folder($row['name'], $folderid);
			$folder->setOpened($row['opened']);
			$children = self::childrenOf($folderid);
			$folder->addChildren($children);
			$folderlist[] = $folder;
		}
		
		return $folderlist;
	}

	/**
	 * @brief Returns the forest (list of trees) of folders children of $parentid, 
	 *		 including the feeds that they contain
	 * @param 
	 * @returns 
	 */
	public function childrenOfWithFeeds($parentid) {
		
		$feedmapper = new FeedMapper();
		$collectionlist = $feedmapper->findByFolderId($parentid);
				
		$stmt = \OCP\DB::prepare('SELECT * FROM ' . self::tableName .
					' WHERE user_id = ? AND parent_id = ?');
		$result = $stmt->execute(array($this->userid, $parentid));
		
		while( $row = $result->fetchRow()) {
			$folderid = $row['id'];
			$folder = new Folder($row['name'], $folderid);
			$folder->setOpened($row['opened']);
			$children = self::childrenOfWithFeeds($folderid);
			$folder->addChildren($children);
			$collectionlist[] = $folder;
		}
		
		return $collectionlist;
	}

	
	/**
	 * This is being used for consistency
	 */
	public function findById($id){
		return $this->find($id);
	}
	

	/**
	 * @brief Retrieve a folder from the database
	 * @param id The id of the folder in the database table.
	 * @returns  an instance of OC_News_Folder
	 */
	public function find($id) {
		$stmt = \OCP\DB::prepare('SELECT *
					FROM ' . self::tableName .
					' WHERE user_id = ? AND id = ?');
		$result = $stmt->execute(array($this->userid, $id));

		$row = $result->fetchRow();
		$folder = new Folder($row['name'], $row['id']);
		$folder->setOpened($row['opened']);

		return $folder;
	}

	/**
	 * @brief Store the folder and all its feeds into the database
	 * @param folder the folder to be saved
	 * @returns The id of the folder in the database table.
	 */
	public function save(Folder $folder) {
		$query = \OCP\DB::prepare('
			INSERT INTO ' . self::tableName .
			'(name, parent_id, user_id, opened)
			VALUES (?, ?, ?, ?)
			');

		$name = $folder->getName();

		if(empty($name)) {
			$l = \OC_L10N::get('news');
			$name = $l->t('no name');
		}

		$parentid = $folder->getParentId();

		$params=array(
			$name,
			$parentid,
			$this->userid,
			$folder->getOpened()
		);
		$query->execute($params);
		$folderid = \OCP\DB::insertid(self::tableName);

		$folder->setId($folderid);
		return $folderid;
	}


	/**
	 * @brief Updates the folder
	 * @param folder the folder to be updated
	 */
	public function update(Folder $folder) {
		$query = \OCP\DB::prepare('UPDATE ' . self::tableName 
			. ' SET name = ?, opened = ?' . ' WHERE id = ?');

		$params = array($folder->getName(), $folder->getOpened(), $folder->getId());
		$query->execute($params);
		return true;
	}

	/**
	 * @brief Delete the folder and all its feeds from the database
	 * @param folder the folder to be deleted (an instance of OCA\News\Folder)
	 * @returns true if the folder has been deleted, false if an error occurred
	 */
	public function delete(Folder $folder) {
		$folderid = $folder->getId();
		return deleteById(folderid);
	}

	/**
	 * @brief Delete the folder and all its feeds from the database
	 * @param folder the folder to be deleted (an instance of OCA\News\Folder)
	 * @returns true if the folder has been deleted, false if an error occurred
	 */
	public function deleteById($folderid) {
		if ($folderid == null) {
			return false;
		}

		// delete child folders
		$stmt = \OCP\DB::prepare('SELECT id FROM ' . self::tableName .' WHERE parent_id = ? AND user_id = ?');
		$result = $stmt->execute(array($folderid, $this->userid));
		while ($row = $result->fetchRow()) {
			if (!self::deleteById($row['id']))
				return false;
		}

		$stmt = \OCP\DB::prepare('DELETE FROM ' . self::tableName .' WHERE id = ? AND user_id = ?');
		$result = $stmt->execute(array($folderid, $this->userid));

		$feedMapper = new FeedMapper($this->userid);
		//TODO: handle the value that the execute returns
		if(!$feedMapper->deleteAll($folderid))
			return false;

		return true;
	}

}