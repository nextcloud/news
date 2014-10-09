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


class Fetcher {

	private $fetchers;

	public function __construct(){
		$this->fetchers = [];
	}


	/**
	 * Add an additional fetcher
	 * @param IFeedFetcher $fetcher the fetcher
	 */
	public function registerFetcher(IFeedFetcher $fetcher){
		$this->fetchers[] = $fetcher;
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
	public function fetch($url, $getFavicon=true){
		foreach($this->fetchers as $fetcher){
			if($fetcher->canHandle($url)){
				return $fetcher->fetch($url, $getFavicon);
			}
		}

		return [null, []];
	}


}