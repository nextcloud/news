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
 * It follows the Data Mapper pattern.
 */
class OC_News_FeedMapper {
	
	/**
	 * @brief Save the feed into the database
	 * @returns The id of the feed in the database table.
	 */
	public function insert(OC_News_Feed $feed) {
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
		$query = OCP\DB::prepare("
			INSERT INTO *PREFIX*news_feeds
			(url, title, added, lastmodified)
			VALUES (?, ?, $_ut, $_ut)
			");
		
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
		
		$feed_id = OCP\DB::insertid('*PREFIX*news_feeds');
		
		return $feed_id;
	}
}