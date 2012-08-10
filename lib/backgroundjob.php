<?php
/**
* ownCloud - News app
*
* @author Jakob Sack
* @copyright 2012 Jakob Sack owncloud@jakobsack.de
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News;

/**
 * This class maps a feed to an entry in the feeds table of the database.
 */
class Backgroundjob {
	static public function sortFeeds( $a, $b ){
		if( $a['id'] == $b['id'] ){
			return 0;
		}
		elseif( $a['id'] < $b['id'] ){
			return -1;
		}
		else{
			return 1;
		}
	}
	
	static public function run(){
		if( \OC::$CLI ){
			self::cliStep();
		}
		else{
			self::webStep();
		}
	}
	
	static private function cliStep(){
		$feedmapper = new \OC_News_FeedMapper();
		
		// Iterate over all feeds
		$feeds = $feedmapper->findAll();
		foreach( $feeds as $feed ){
			self::updateFeed( $feedmapper, $feed );
		}
	}
	
	static private function webStep(){
		// Iterate over all users
		$lastid = \OCP\Config::getAppValue('news', 'backgroundjob_lastid',0);
		
		$feedmapper = new \OC_News_FeedMapper();
		$feeds = $feedmapper->findAll();
		usort( $feeds, array( 'OCA\News\Backgroundjob', 'sortFeeds' ));
		
		$done = false;
		foreach( $feeds as $feed ){
			if( $feed['id'] > $lastid ){
				// set lastid BEFORE updating feed!
				\OCP\Config::setAppValue('news', 'backgroundjob_lastid',$feed['id']);
				$done = true;
				self::updateFeed( $feedmapper, $feed );
			}
		}
		
		if( !$done ){
			\OCP\Config::setAppValue('news', 'backgroundjob_lastid',0);
		}
	}
		
	static private function updateFeed( $feedmapper, $feed ){
		$newfeed = null;
		$newfeed = \OC_News_Utils::fetch( $feed['url'] );
		if( $newfeed !== null ){
			$feedmapper = new \OC_News_FeedMapper();
			$newfeedid = $feedmapper->save($newfeed, $feed['folderid'] );
		}
	}
}
