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
use \OCA\AppFramework\Utility\SimplePieAPIFactory;
use \OCA\AppFramework\Utility\TimeFactory;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;


class FeedFetcher implements IFeedFetcher {

	private $api;
	private $cacheDirectory;
	private $cacheDuration;
	private $faviconFetcher;
	private $simplePieFactory;
	private $fetchTimeout;
	private $time;
	private $purifier;

	public function __construct(API $api,
				    SimplePieAPIFactory $simplePieFactory,
				    FaviconFetcher $faviconFetcher,
				    TimeFactory $time,
				    $cacheDirectory,
				    $cacheDuration,
				    $fetchTimeout,
				    $purifier){
		$this->api = $api;
		$this->cacheDirectory = $cacheDirectory;
		$this->cacheDuration = $cacheDuration;
		$this->faviconFetcher = $faviconFetcher;
		$this->simplePieFactory = $simplePieFactory;
		$this->time = $time;
		$this->purifier = $purifier;
		$this->fetchTimeout = $fetchTimeout;
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
	public function fetch($url, $getFavicon=true) {
		$simplePie = $this->simplePieFactory->getCore();
		$simplePie->set_feed_url($url);
		$simplePie->enable_cache(true);
		$simplePie->set_timeout($this->fetchTimeout);
		$simplePie->set_cache_location($this->cacheDirectory);
		$simplePie->set_cache_duration($this->cacheDuration);

		if (!$simplePie->init()) {
			throw new FetcherException('Could not initialize simple pie on feed with url ' . $url);
		}


		try {
			// somehow $simplePie turns into a feed after init
			$items = array();
			if ($feedItems = $simplePie->get_items()) {
				foreach($feedItems as $feedItem) {
					array_push($items, $this->buildItem($feedItem));
				}
			}

			$feed = $this->buildFeed($simplePie, $url, $getFavicon);

			return array($feed, $items);

		} catch(\Exception $ex){
			throw new FetcherException($ex->getMessage());
		}

	}


	protected function buildItem($simplePieItem) {
		$item = new Item();
		$item->setStatus(0);
		$item->setUnread();
		$item->setUrl(html_entity_decode($simplePieItem->get_permalink(),
			ENT_COMPAT, 'UTF-8'));
		// unescape content because angularjs helps against XSS
		$item->setTitle(html_entity_decode($simplePieItem->get_title(),
			ENT_COMPAT, 'UTF-8'));
		$guid = $simplePieItem->get_id();
		$item->setGuid($guid);
		$item->setGuidHash(md5($guid));
		$item->setBody(str_replace('<a', '<a target="_blank"',
		// escape XSS
		$this->purifier->purify($simplePieItem->get_content())));
		$item->setPubDate($simplePieItem->get_date('U'));
		$item->setLastModified($this->time->getTime());

		$author = $simplePieItem->get_author();
		if ($author !== null) {
			$name = html_entity_decode($author->get_name(),
				ENT_COMPAT, 'UTF-8' );
			if ($name) {
				$item->setAuthor($name);
			} else {
				$item->setAuthor(html_entity_decode($author->get_email()),
					ENT_COMPAT, 'UTF-8' );
			}
		}

		// TODO: make it work for video files also
		$enclosure = $simplePieItem->get_enclosure();
		if($enclosure !== null) {
			$enclosureType = $enclosure->get_type();
			if(stripos($enclosureType, "audio/") !== false) {
				$item->setEnclosureMime($enclosureType);
				$item->setEnclosureLink($enclosure->get_link());
			}
		}

		return $item;
	}


	protected function buildFeed($simplePieFeed, $url, $getFavicon) {
		$feed = new Feed();

		// unescape content because angularjs helps against XSS
		$title = html_entity_decode($simplePieFeed->get_title(),
			ENT_COMPAT, 'UTF-8' );

		// if there is no title use the url
		if(!$title) {
			$title = $url;
		}

		$feed->setTitle($title);
		$feed->setUrl($url);
		$feed->setLink($simplePieFeed->get_link());
		$feed->setUrlHash(md5($url));
		$feed->setAdded($this->time->getTime());

		if ($getFavicon) {
			// use the favicon from the page first since most feeds use a weird image
			$favicon = $this->faviconFetcher->fetch($feed->getLink());

			if (!$favicon) {
				$favicon = $simplePieFeed->get_image_url();
			}

			$feed->setFaviconLink($favicon);
		}

		return $feed;
	}

}
