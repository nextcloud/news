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


class EscapistComicEnhancer extends ArticleEnhancer {


	public function __construct(SimplePieFileFactory $fileFactory, $purifier,
								$timeout) {
		parent::__construct(
			$purifier,
			$fileFactory,
			array(
				'/escapistmagazine.com\/articles\/view\/comics\/critical-miss/' => '//*[@class=\'body\']/span/img',
				'/escapistmagazine.com\/articles\/view\/comics\/namegame/' => '//*[@class=\'body\']/span/p/img[@height != "120"]',
				'/escapistmagazine.com\/articles\/view\/comics\/(stolen-pixels|bumhugparade|escapistradiotheater)/' => '//*[@class=\'body\']/span/p[2]/img',
				'/escapistmagazine.com\/articles\/view\/comics\/paused/' => '//*[@class=\'body\']/span/p[2]/img | //*[@class=\'body\']/span/div/img',
				'/escapistmagazine.com\/articles\/view\/comics\/fraughtwithperil/' => '//*[@class=\'body\']',
			),
			$timeout
		);
	}
}
