<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

namespace OCA\News\Utility;


class FeedFetcher {


	/**
	 * @brief Fetch a feed from remote
	 * @param url remote url of the feed
	 * @returns an instance of OC_News_Feed
	 */
	public function fetch($url) {
		$spfeed = new \SimplePie_Core();
		$spfeed->set_feed_url( $url );
		$spfeed->enable_cache( false );

		if (!$spfeed->init()) {
			return null;
		}

		//temporary try-catch to bypass SimplePie bugs
		try {
			$spfeed->handle_content_type();
			$title = $spfeed->get_title();

			$items = array();
			if ($spitems = $spfeed->get_items()) {
				foreach($spitems as $spitem) {
					$itemUrl = $spitem->get_permalink();
					$itemTitle = $spitem->get_title();
					$itemGUID = $spitem->get_id();
					$itemBody = $spitem->get_content();
					$item = new Item($itemUrl, $itemTitle, $itemGUID, $itemBody);
					
					$spAuthor = $spitem->get_author();
					if ($spAuthor !== null) {
						$item->setAuthor($spAuthor->get_name());
					}

					//date in Item is stored in UNIX timestamp format
					$itemDate = $spitem->get_date('U');
					$item->setDate($itemDate);

					// associated media file, for podcasts
					$itemEnclosure = $spitem->get_enclosure();
					if($itemEnclosure !== null) {
						$enclosureType = $itemEnclosure->get_type();
						$enclosureLink = $itemEnclosure->get_link();
						if(stripos($enclosureType, "audio/") !== FALSE) {
							$enclosure = new Enclosure();
							$enclosure->setMimeType($enclosureType);
							$enclosure->setLink($enclosureLink);
							$item->setEnclosure($enclosure);
						}
					}
					
					$items[] = $item;
				}
			}

			$feed = new Feed($url, $title, $items);

			$favicon = $spfeed->get_image_url();

			if ($favicon !== null && $this->checkFavicon($favicon)) { // use favicon from feed
				$feed->setFavicon($favicon);
			}
			else { // try really hard to find a favicon
				$webFavicon = $this->discoverFavicon($url);
				if ($webFavicon !== null) {
					$feed->setFavicon($webFavicon);
				}
			}
			return $feed;
		}
	  catch (Exception $e) {
			return null;
	  }
	}

	/**
	 * Perform a "slim" fetch of a feed from remote.
	 * Differently from Utils::fetch(), it doesn't retrieve items nor a favicon
	 *
	 * @param url remote url of the feed
	 * @returns an instance of OC_News_Feed
	 */
	public function slimFetch($url) {
		$spfeed = new \SimplePie_Core();
		$spfeed->set_feed_url( $url );
		$spfeed->enable_cache( false );
		$spfeed->set_stupidly_fast( true );

		if (!$spfeed->init()) {
			return null;
		}

	   //temporary try-catch to bypass SimplePie bugs
	   try {
		$title = $spfeed->get_title();

		$feed = new Feed($url, $title);

		return $feed;
		}
	   catch (Exception $e) {
		return null;
	   }
	}

	public function checkFavicon($favicon) {
		if ($favicon === null || $favicon == false)
			return false;

		$file = new \SimplePie_File($favicon);
		// size in bytes
		$filesize = strlen($file->body);

		if($file->success && $filesize > 0 && $filesize < 50000) { //bigger files are not considered favicons
			$sniffer = new \SimplePie_Content_Type_Sniffer($file);
			if(substr($sniffer->get_type(), 0, 6) === 'image/') {
				$imgsize = getimagesize($favicon);
				if ($imgsize['0'] <= 32 && $imgsize['1'] <= 32) { //bigger images are not considered favicons
					return true;
				}
			}
		}
		return false;
	}

	public function discoverFavicon($url) {
		//try webroot favicon
		$favicon = \SimplePie_Misc::absolutize_url('/favicon.ico', $url);

		if($this->checkFavicon($favicon))
			return $favicon;

		//try to extract favicon from web page
		$absoluteUrl = \SimplePie_Misc::absolutize_url('/', $url);

		$handle = curl_init ( );
		curl_setopt ( $handle, CURLOPT_URL, $absoluteUrl );
		curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $handle, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt ( $handle, CURLOPT_MAXREDIRS, 10 );

		if ( FALSE!==($page=curl_exec($handle)) ) {
			preg_match ( '/<[^>]*link[^>]*(rel=["\']icon["\']|rel=["\']shortcut icon["\']) .*href=["\']([^>]*)["\'].*>/iU', $page, $match );
			if (1<sizeof($match)) {
				// the specified uri might be an url, an absolute or a relative path
				// we have to turn it into an url to be able to display it out of context
				$favicon = htmlspecialchars_decode ( $match[2] );
				// test for an url
				if (parse_url($favicon,PHP_URL_SCHEME)) {
					if($this->checkFavicon($favicon))
						return $favicon;
				}
			}
		}
		return null;
	}
}