<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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

namespace OCA\News\ArticleEnhancer;


class Enhancer {

	private $enhancers = array();

	public function registerEnhancer($feedUrl, ArticleEnhancer $enhancer){
		$feedUrl = $this->removeTrailingSlash($feedUrl);

		// create hashkeys for all supported protocols for quick access
		$this->enhancers[$feedUrl] = $enhancer;
		$this->enhancers['https://' . $feedUrl] = $enhancer;
		$this->enhancers['http://' . $feedUrl] = $enhancer;
		$this->enhancers['https://www.' . $feedUrl] = $enhancer;
		$this->enhancers['http://www.' . $feedUrl] = $enhancer;
	}


	public function enhance($item, $feedUrl){
		$feedUrl = $this->removeTrailingSlash($feedUrl);

		if(array_key_exists($feedUrl, $this->enhancers)) {
			return $this->enhancers[$feedUrl]->enhance($item);
		} else {
			return $item;
		}
	}


	private function removeTrailingSlash($url) {
		if($url[strlen($url)-1] === '/') {
			return substr($url, 0, -1);
		} else {
			return $url;
		}
	}


}