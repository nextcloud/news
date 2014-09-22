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

namespace OCA\News\ArticleEnhancer;


class Enhancer {

	private $enhancers = [];
	private $globalEnhancers = [];

	/**
	 * @param string $feedUrl
	 * @param ArticleEnhancer $enhancer
	 */
	public function registerEnhancer($feedUrl, ArticleEnhancer $enhancer){
		$feedUrl = $this->removeTrailingSlash($feedUrl);

		// create hashkeys for all supported protocols for quick access
		$this->enhancers[$feedUrl] = $enhancer;
		$this->enhancers['https://' . $feedUrl] = $enhancer;
		$this->enhancers['http://' . $feedUrl] = $enhancer;
		$this->enhancers['https://www.' . $feedUrl] = $enhancer;
		$this->enhancers['http://www.' . $feedUrl] = $enhancer;
	}


	/**
	 * Registers enhancers that are run for every item and after all previous
	 * enhancers have been run
	 * @param ArticleEnhancer $enhancer
	 */
	public function registerGlobalEnhancer (ArticleEnhancer $enhancer) {
		$this->globalEnhancers[] = $enhancer;
	}


	/**
	 * @param \OCA\News\Db\Item $item
	 * @param string $feedUrl
	 * @return \OCA\News\Db\Item enhanced item
	 */
	public function enhance($item, $feedUrl){
		$feedUrl = $this->removeTrailingSlash($feedUrl);

		if(array_key_exists($feedUrl, $this->enhancers)) {
			$result = $this->enhancers[$feedUrl]->enhance($item);
		} else {
			$result = $item;
		}

		foreach ($this->globalEnhancers as $enhancer) {
			$result = $enhancer->enhance($result);
		}

		return $result;
	}


    /**
     * @param string $url
     * @return string
     */
	private function removeTrailingSlash($url) {
		if($url[strlen($url)-1] === '/') {
			return substr($url, 0, -1);
		} else {
			return $url;
		}
	}


}