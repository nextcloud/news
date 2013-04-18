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

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Utility\FaviconFetcher;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;


class FeedFetcher implements IFeedFetcher {

	private $api;
	private $cacheDirectory;
	private $cacheDuration;
	private $faviconFetcher;

	public function __construct(API $api, FaviconFetcher $faviconFetcher,
	                            $cacheDirectory, $cacheDuration){
		$this->api = $api;
		$this->cacheDirectory = $cacheDirectory;
		$this->cacheDuration = $cacheDuration;
		$this->faviconFetcher = $faviconFetcher;
	}


	/**
	 * This fetcher handles all the remaining urls therefore always returns true
	 */
	public function canHandle($url){
		return true;
	}


	/**
	 * Fetch a feed from remote
	 * @param string url remote url of the feed
	 * @throws FetcherException if simple pie fails
	 * @return array an array containing the new feed and its items
	 */
	public function fetch($url) {
		// TODO: write unittests!
		$simplePie = new \SimplePie_Core();
		$simplePie->set_feed_url( $url );
		$simplePie->enable_cache(true);
		$simplePie->set_cache_location($this->cacheDirectory);
		$simplePie->set_cache_duration($this->cacheDuration);
		
		if (!$simplePie->init()) {
			throw new FetcherException('Could not initialize simple pie');
		}

		try {
			$items = array();
			if ($feedItems = $simplePie->get_items()) {
				foreach($feedItems as $feedItem) {
					$item = new Item();
					$item->setStatus(0);
					$item->setUnread();
					$item->setUrl( $feedItem->get_permalink() );
					// unescape content because angularjs helps agains XSS
					$item->setTitle(html_entity_decode($feedItem->get_title()));
					$item->setGuid( $feedItem->get_id() );
					$item->setGuidHash( md5($feedItem->get_id()) );
					$item->setBody( $feedItem->get_content() );
					$item->setPubDate( $feedItem->get_date('U') );
					$item->setLastModified(time());

					$author = $feedItem->get_author();
					if ($author !== null) {
						$item->setAuthor( $author->get_name() );
					}

					// TODO: make it work for video files also
					$enclosure = $feedItem->get_enclosure();
					if($enclosure !== null) {
						$enclosureType = $enclosure->get_type();
						if(stripos($enclosureType, "audio/") !== false) {
							$item->setEnclosureMime($enclosureType);
							$item->setEnclosureLink($enclosure->get_link());
						}
					}
					
					array_push($items, $item);
				}
			}

			$feed = new Feed();
			// unescape content because angularjs helps agains XSS
			$feed->setTitle(html_entity_decode($simplePie->get_title()));
			$feed->setUrl($url);
			$feed->setLink($simplePie->get_link());
			$feed->setUrlHash(md5($url));
			$feed->setAdded(time());

			// get the favicon from the feed
			$favicon = $simplePie->get_image_url();
			if ($favicon) {
				$feed->setFaviconLink($favicon);

			// or the webpage
			} else {
				$webFavicon = $this->faviconFetcher->fetch($feed->getLink());
				$feed->setFaviconLink($webFavicon);
			}

			return array($feed, $items);

		} catch(\Exception $ex){
			throw new FetcherException($ex->getMessage());
		}

	}


}