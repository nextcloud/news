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


class OC_News_Utils {

	/**
	 * @brief Fetch a feed from remote
	 * @param url remote url of the feed 
	 * @returns 
	 */
	public static function fetch($url){
		$spfeed = new SimplePie_Core();
		$spfeed->set_feed_url( $url );
		$spfeed->enable_cache( false );
		$spfeed->init();
		$spfeed->handle_content_type();
		$title = $spfeed->get_title();
		
		$spitems = $spfeed->get_items();
		$items = array();
		foreach($spitems as $spitem) { //FIXME: maybe we can avoid this loop
			$itemUrl = $spitem->get_permalink();
			$itemTitle = $spitem->get_title();
			$items[] = new OC_News_Item($itemUrl, $itemTitle); 
		}

		$feed = new OC_News_Feed($url, $title, $items);
		return $feed;
	}
}