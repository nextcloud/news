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
	 * Fetch a feed from remote
	 * @param string url remote url of the feed
	 * @throws FetcherException if simple pie fails
	 * @returns array an array containing the new feed and its items
	 */
	public function fetch($url) {
		$simplePie = new \SimplePie_Core();
		$simplePie->set_feed_url( $url );
		$simplePie->enable_cache( false );

		// TODO: throw error
		if (!$simplePie->init()) {
			return null;
		}

		// temporary try-catch to bypass SimplePie bugs
		try {
			$simplePie->handle_content_type();

			$items = array();
			if ($feedItems = $simplePie->get_items()) {
				foreach($feedItems as $feedItem) {
					$item = new Item();
					$item->setUrl( $feedItem->get_permalink() );
					$item->setTitle( $feedItem->get_title() );
					$item->setGuid( $feedItem->get_id() );
					$item->setBody( $feedItem->get_content() );
					$item->setDate( $feedItem->get_date('U') );

					$author = $feedItem->get_author();
					if ($author !== null) {
						$item->setAuthor( $author->get_name() );
					}

					// associated media file, for podcasts
					$enclosure = $feedItem->get_enclosure();
					if($enclosure !== null) {
						$enclosureType = $enclosure->get_type();
						if(stripos($enclosureType, "audio/") !== false) {
							$enclosure->setEnclosureMime($enclosureType);
							$enclosure->setEnclosureLink($enclosure->get_link());
						}
					}
					
					array_push($items, $item);
				}
			}

			$feed = new Feed();
			$feed->setTitle( $simplePie->get_title());
			$feed->setUrl($url);
			$feed->setUrlHash(md5($url));
			$feed->setAdded(time());

			$favicon = $simplePie->get_image_url();

			if ($favicon !== null && $this->checkFavicon($favicon)) {
				$feed->setFaviconLink($favicon);

			} else {
				$webFavicon = $this->discoverFavicon($url);
				if ($webFavicon !== null) {
					$feed->setFaviconLink($webFavicon);
				}
			}
			return array($feed, $items);
		} catch(Exception $ex){
			throw new FetcherException($ex->getMessage());
		}

	}

	/**
	 * Perform a "slim" fetch of a feed from remote.
	 * Differently from Utils::fetch(), it doesn't retrieve items nor a favicon
	 *
	 * @param string url remote url of the feed
	 * @returns \OCA\News\Db\Feed
	 */
	public function slimFetch($url) {
		$simplePie = new \SimplePie_Core();
		$simplePie->set_feed_url( $url );
		$simplePie->enable_cache( false );
		$simplePie->set_stupidly_fast( true );

		if (!$simplePie->init()) {
			return null;
		}

		// temporary try-catch to bypass SimplePie bugs
		try {
			$feed = new Feed($url, $title);
			$feed->setUrl($url);
			$feed->setUrlHash(md5($url));
			$feed->setTitle($simplePie->get_title());
			$feed->setAdded(time());
			return $feed;

		} catch(Exception $ex){
			throw new FetcherException($ex->getMessage());
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