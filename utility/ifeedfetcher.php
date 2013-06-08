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

interface IFeedFetcher {

	/**
	 * @param string url the url that the user entered in the add feed dialog
	 * box
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