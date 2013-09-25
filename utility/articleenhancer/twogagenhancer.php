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

namespace OCA\News\Utility\ArticleEnhancer;

use \OCA\News\Utility\SimplePieFileFactory;


class TwoGAGEnhancer extends ArticleEnhancer {


	public function __construct(SimplePieFileFactory $fileFactory, $purifier,
								$timeout) {
		parent::__construct(
			$purifier,
			$fileFactory,
			array(),
			$timeout
		);
	}

	public function enhance($item) {
		if (preg_match('/www.twogag.com\/archives/', $item->getUrl()) || preg_match('/feedproxy.google.com\/\~r\/TwoGuysAndGuy/', $item->getUrl())) {
			$body = $item->getBody();
			$body = preg_replace('/http\:\/\/www.twogag.com\/comics-rss\/([^.]+)\.jpg/', 'http://www.twogag.com/comics/$1.jpg', $body);
			$item->setBody($body);
		}
		return $item;
	}
}
