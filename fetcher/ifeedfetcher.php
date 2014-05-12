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

interface IFeedFetcher {

	/**
	 * @param string url remote url of the feed
	 * @param boolean $getFavicon if the favicon should also be fetched, defaults
	 * to true
	 * @throws FetcherException if the fetcher encounters a problem
	 * @return array with the first element being the feed and the
	 * second element being an array of items. Those items will be saved into
	 * into the database
	 */
	function fetch($url, $getFavicon=true);

	/**
	 * @param string $url the url that should be fetched
	 * @return boolean if the fetcher can handle the url. This fetcher will be
	 * used exclusively to fetch the feed and the items of the page
	 */
	function canHandle($url);

}