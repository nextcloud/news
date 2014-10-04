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

namespace OCA\News\Feed\Client;

class HttpClient {

	protected $defaults = [
		'user_agent' => 'ownCloud News/VERSION ' .
			'(+https://owncloud.org/; 1 subscriber; feed-url=URL)',
		'connection_timeout' => 10,  // seconds
		'timeout' => 10,  // seconds
		'verify_ssl' => true,
		'http_version' => '1.1',
		'proxy_host' => '',
		'proxy_port' => 80,
		'proxy_user' => '',
		'proxy_password' => ''
	];

	public function __construct ($version, array $config=null) {
		foreach ($config as $key => $value) {
			$this->defaults[$key] = $value;
		}

		$this->defaults['user_agent'] = str_replace('VERSION', $version,
			$this->defaults['user_agent']);
	}


	public abstract function get($url);

}


