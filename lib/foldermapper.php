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

	public function __construct($userid){
		$this->userid = $userid;
	}
	
	public function root(){
		$root = new OC_News_Folder('All feeds');
		$stmt = OCP\DB::prepare('SELECT * 
					FROM ' . self::tableName . 
					' WHERE user_id = ? AND parent_id = ?');
		$result = $stmt->execute(array($this->userid, 0));
		
		while( $row = $result->fetchRow()){
			$child = new OC_News_Folder($row['name'], $row['id']);
			$root->addChild($child);
		}
		
		$feedmapper = new OC_News_FeedMapper();
		$feeds = $feedmapper->findByFolderId(0);
		foreach ($feeds as $feed){
			$root->addChild($feed);
		}
		
		return $root;
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
		$result = $stmt->execute(array($this->userid, 0));
		
		while( $row = $result->fetchRow()){
			$child = new OC_News_Folder($row['name'], $row['id']);
			$root->addChild($child);
		}

		return $root;
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

	}
	
	//TODO: replace it with a DELETE INNER JOIN operation
	public function delete(OC_News_Folder $folder){
		$id = $folder->getId();
	
		$stmt = OCP\DB::prepare("
			DELETE FROM " . self::tableName . 
			"WHERE id = $id
			");

		$result = $stmt->execute();
		
		$feedMapper = new OC_News_FeedMapper();
		//TODO: handle the value that the execute returns
		$feedMapper->deleteAll($id);
		
		return true;
	}
	
}