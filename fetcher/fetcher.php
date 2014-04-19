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
		$this->fetchers = array();
	}


	public function registerFetcher(IFeedFetcher $fetcher){
		array_push($this->fetchers, $fetcher);
	}


	public function fetch($url, $getFavicon=true){
		foreach($this->fetchers as $fetcher){
			if($fetcher->canHandle($url)){
				return $fetcher->fetch($url, $getFavicon);
			}
		}
	}


}