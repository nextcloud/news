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
	 * @brief Save the feed and all its items into the database
	 * @param feed the feed to be saved
	 * @returns The id of the feed in the database table.
	 */
	public function insert(OC_News_Feed $feed){
		$CONFIG_DBTYPE = OCP\Config::getSystemValue( "dbtype", "sqlite" );
		if( $CONFIG_DBTYPE == 'sqlite' or $CONFIG_DBTYPE == 'sqlite3' ){
			$_ut = "strftime('%s','now')";
		} elseif($CONFIG_DBTYPE == 'pgsql') {
			$_ut = 'date_part(\'epoch\',now())::integer';
		} else {
			$_ut = "UNIX_TIMESTAMP()";
		}
		
		//FIXME: Detect when user adds a known feed
		//FIXME: specify folder where you want to add
		$query = OCP\DB::prepare('
			INSERT INTO ' . self::tableName .
			'(url, title, added, lastmodified)
			VALUES (?, ?, $_ut, $_ut)
			');
		
		$title = $feed->getTitle();

		if(empty($title)) {
			$l = OC_L10N::get('news');
			$title = $l->t('no title');
		}

		$params=array(
		htmlspecialchars_decode($feed->getUrl()),
		htmlspecialchars_decode($title)
		
		//FIXME: user_id is going to move to the folder properties
		//OCP\USER::getUser()
		);
		$query->execute($params);
		
		$feedid = OCP\DB::insertid(self::tableName);
		$feed->setId($feedid);

		$itemMapper = new OC_News_ItemMapper($feed);
		
		$items = $feed->getItems();
		foreach($items as $item){
			$itemMapper->insert($item);
		}
		return $feedid;
	}
