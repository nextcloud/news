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


namespace OCA\News\Utility;


class SimplePieAPIFactory {

    /**
     * Builds a simplepie file object. This is needed because
     * the file object contains logic in its constructor which makes it
     * impossible to inject and test
     *
     * @param $url
     * @param int $timeout
     * @param int $redirects
     * @param null $headers
     * @param null $useragent
     * @param bool $force_fsockopen
     * @param null $proxyHost
     * @param null $proxyPort
     * @param null $proxyAuth
     * @return SimplePie_File a new object
     */
	public function getFile($url, $timeout=10, $redirects=5, $headers=null,
	                        $useragent=null, $force_fsockopen=false,
	                        $proxyHost=null, $proxyPort=null, $proxyAuth=null) {

		return new \SimplePie_File($url, $timeout, $redirects, $headers,
	                        $useragent, $force_fsockopen, $proxyHost, $proxyPort,
	                        $proxyAuth);
	}


	/**
	 * Returns a new instance of a SimplePie_Core() object. This is needed
	 * because the class relies on external dependencies which are not passed
	 * in via the constructor and thus making it nearly impossible to unit test
	 * code that uses this class
	 * @return \SimplePie_Core instance
	 */
	public function getCore() {
		return new \SimplePie();
	}


}