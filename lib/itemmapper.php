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

	private $tableName = '*PREFIX*news_items';
	private $feed;

	public function __construct(OC_News_Feed $feed){
		$this->feed = $feed;
	}

	/**
	 * @brief Retrieve a feed from the database
	 * @param id The id of the feed in the database table.
	 */
	public function find($id){
		$stmt = OCP\DB::prepare('SELECT * FROM ' . $this->feedTableName . ' WHERE id = ?');
		$result = $stmt->execute(array($id));
		$row = $result->fetchRow();

		$url = $row['url'];
		$title = $row['title'];

	}

	/**
	 * @brief Save the feed and all its items into the database
	 * @returns The id of the feed in the database table.
	 */
	public function insert(OC_News_Item $item){
		
		$feedid = $this->feed->getId();

		$query = OCP\DB::prepare('
			INSERT INTO ' . $this->tableName .
			'(url, title, feedid)
			VALUES (?, ?, $feedid)
			');

		$title = $item->getTitle();
echo $title;
		if(empty($title)) {
			$l = OC_L10N::get('news');
			$title = $l->t('no title');
		}

		$params=array(
		htmlspecialchars_decode($feed->getUrl()),
		htmlspecialchars_decode($title)
		);
		$query->execute($params);
		
		$itemid = OCP\DB::insertid($this->tableName);
		$item->setId($itemid);
		return $itemid;
	}
}