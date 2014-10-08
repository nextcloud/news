<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Fetcher;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Utility\FaviconFetcher;
use \OCA\News\Utility\SimplePieAPIFactory;
use \OCA\News\Utility\Config;
use \OCA\News\Config\AppConfig;


class FeedFetcher implements IFeedFetcher {

	private $cacheDirectory;
	private $cacheDuration;
	private $faviconFetcher;
	private $simplePieFactory;
	private $fetchTimeout;
	private $time;
	private $proxyHost;
	private $proxyPort;
	private $proxyAuth;
	private $appConfig;

	public function __construct(SimplePieAPIFactory $simplePieFactory,
				    FaviconFetcher $faviconFetcher,
				    $time,
				    $cacheDirectory,
				    Config $config,
				    AppConfig $appConfig){
		$this->cacheDirectory = $cacheDirectory;
		$this->cacheDuration = $config->getSimplePieCacheDuration();
		$this->fetchTimeout = $config->getFeedFetcherTimeout();
		$this->faviconFetcher = $faviconFetcher;
		$this->simplePieFactory = $simplePieFactory;
		$this->time = $time;
		$this->proxyHost = $config->getProxyHost();
		$this->proxyPort = $config->getProxyPort();
		$this->proxyAuth = $config->getProxyAuth();
		$this->appConfig = $appConfig;
	}


	/**
	 * This fetcher handles all the remaining urls therefore always returns true
	 */
	public function canHandle($url){
		return true;
	}


	/**
	 * Fetch a feed from remote
	 * @param string $url remote url of the feed
	 * @param boolean $getFavicon if the favicon should also be fetched, defaults
	 * to true
	 * @throws FetcherException if simple pie fails
	 * @return array an array containing the new feed and its items, first
	 * element being the Feed and second element being an array of Items
	 */
	public function fetch($url, $getFavicon=true) {
		$simplePie = $this->simplePieFactory->getCore();
		$simplePie->set_feed_url($url);
		$simplePie->enable_cache(true);
		$simplePie->set_useragent('ownCloud News/' .
				$this->appConfig->getConfig('version') .
				' (+https://owncloud.org/; 1 subscriber; feed-url=' . $url . ')');
		$simplePie->set_stupidly_fast(true);  // disable simple pie sanitation
		                                      // we use htmlpurifier
		$simplePie->set_timeout($this->fetchTimeout);
		$simplePie->set_cache_location($this->cacheDirectory);
		$simplePie->set_cache_duration($this->cacheDuration);

		if(trim($this->proxyHost) !== '') {
			$simplePie->set_proxyhost($this->proxyHost);
			$simplePie->set_proxyport($this->proxyPort);
			$simplePie->set_proxyuserpwd($this->proxyAuth);
		}

		try {
			if (!$simplePie->init()) {
				throw new \Exception('Could not initialize simple pie on feed with url ' . $url);
			}

			// somehow $simplePie turns into a feed after init
			$items = [];
			$permaLink = $simplePie->get_permalink();
			if ($feedItems = $simplePie->get_items()) {
				foreach($feedItems as $feedItem) {
					array_push($items, $this->buildItem($feedItem, $permaLink));
				}
			}

			$feed = $this->buildFeed($simplePie, $url, $getFavicon);

			return [$feed, $items];

		} catch(\Exception $ex){
			throw new FetcherException($ex->getMessage());
		}

	}


	private function decodeTwice($string) {
		// behold! &apos; is not converted by PHP that's why we need to do it
		// manually (TM)
		return str_replace('&apos;', '\'',
				html_entity_decode(
					html_entity_decode(
						$string, ENT_QUOTES, 'UTF-8'
					),
				ENT_QUOTES, 'UTF-8'
			)
		);
	}


	protected function buildItem($simplePieItem, $feedLink) {
		$item = new Item();
		$item->setStatus(0);
		$item->setUnread();
		$url = $this->decodeTwice($simplePieItem->get_permalink());
		if (!$url) {
			$url = $feedLink;
		}
		$item->setUrl($url);

		// unescape content because angularjs helps against XSS
		$item->setTitle($this->decodeTwice($simplePieItem->get_title()));
		$guid = $simplePieItem->get_id();
		$item->setGuid($guid);

		// purification is done in the service layer
		$item->setBody($simplePieItem->get_content());

		// pubdate is not required. if not given use the current date
		$date = $simplePieItem->get_date('U');
		if(!$date) {
			$date = $this->time->getTime();
		}

		$item->setPubDate($date);

		$item->setLastModified($this->time->getTime());

		$author = $simplePieItem->get_author();
		if ($author !== null) {
			$name = $this->decodeTwice($author->get_name());
			if ($name) {
				$item->setAuthor($name);
			} else {
				$item->setAuthor($this->decodeTwice($author->get_email()));
			}
		}

		// TODO: make it work for video files also
		$enclosure = $simplePieItem->get_enclosure();
		if($enclosure !== null) {
			$enclosureType = $enclosure->get_type();
			if(stripos($enclosureType, 'audio/') !== false ||
			   stripos($enclosureType, 'video/') !== false) {
				$item->setEnclosureMime($enclosureType);
				$item->setEnclosureLink($enclosure->get_link());
			}
		}

		return $item;
	}


	protected function buildFeed($simplePieFeed, $url, $getFavicon) {
		$feed = new Feed();

		// unescape content because angularjs helps against XSS
		$title = strip_tags($this->decodeTwice($simplePieFeed->get_title()));

		// if there is no title use the url
		if(!$title) {
			$title = $url;
		}

		$feed->setTitle($title);
		$feed->setUrl($url);

		$link = $simplePieFeed->get_permalink();
		if (!$link) {
			$link = $url;
		}
		$feed->setLink($link);

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
