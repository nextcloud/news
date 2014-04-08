<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
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


namespace OCA\News\Utility;


class SimplePieAPIFactory {

	/**
	 * Builds a simplepie file object. This is needed because
	 * the file object contains logic in its constructor which makes it
	 * impossible to inject and test
	 * @return SimplePie_File a new object
	 */
	public function getFile($url, $timeout=10, $redirects=5, $headers=null,
	                        $useragent=null, $force_fsockopen=false) {

		return new \SimplePie_File($url, $timeout, $redirects, $headers,
	                        $useragent, $force_fsockopen);
	}


	/**
	 * Returns a new instance of a SimplePie_Core() object. This is needed
	 * because the class relies on external dependencies which are not passed
	 * in via the constructor and thus making it nearly impossible to unittest
	 * code that uses this class
	 * @return \SimplePie_Core instance
	 */
	public function getCore() {
		return new \SimplePie_Core();
	}


}