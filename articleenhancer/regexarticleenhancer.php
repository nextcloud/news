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

use \OCA\News\Db\Item;


class RegexArticleEnhancer implements ArticleEnhancer {

	private $matchArticleUrl;
	private $regexPair;

	public function __construct($matchArticleUrl, array $regexPair) {
		$this->matchArticleUrl = $matchArticleUrl;
		$this->regexPair = $regexPair;
	}


	/**
	 * @param \OCA\News\Db\Item $item
	 * @return \OCA\News\Db\Item enhanced item
	 */
	public function enhance(Item $item) {
		if (preg_match($this->matchArticleUrl, $item->getUrl())) {
			$body = $item->getBody();
			foreach($this->regexPair as $search => $replaceWith) { 
				$body = preg_replace($search, $replaceWith, $body);
			}
			$item->setBody($body);
		}
		return $item;
	}


}
