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
			$itemGUID = $spitem->get_id();
			$items[] = new OC_News_Item($itemUrl, $itemTitle, $itemGUID); 
		}

		$feed = new OC_News_Feed($url, $title, $items);
		return $feed;
	}
}