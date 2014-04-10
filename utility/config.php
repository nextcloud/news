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

namespace OCA\News\Utility;

use \OCA\News\Core\API;


class Config {

	private $fileSystem; 
	private $autoPurgeMinimumInterval;  // seconds, used to define how
	                                    // long deleted folders and feeds
	                                    // should still be kept for an
	                                    // undo actions
	private $autoPurgeCount;  // number of allowed unread articles per feed
	private $simplePieCacheDuration;  // seconds
	private $feedFetcherTimeout;  // seconds
	private $useCronUpdates;  // turn off updates run by owncloud cronjob
	private $proxyHost;
	private $proxyPort;
	private $proxyAuth;
	private $api;


	public function __construct($fileSystem, API $api) {
		$this->fileSystem = $fileSystem;
		$this->autoPurgeMinimumInterval = 60;
		$this->autoPurgeCount = 200;
		$this->simplePieCacheDuration = 30*60;
		$this->feedFetcherTimeout = 60;
		$this->useCronUpdates = true;
		$this->api = $api;
		$this->proxyHost = '';
		$this->proxyPort = 8080;
		$this->proxyAuth = '';
	}

	public function getProxyPort() {
		return $this->proxyPort;
	}

	public function getProxyHost() {
		return $this->proxyHost;
	}

	public function getProxyAuth() {
		return $this->proxyAuth;
	}

	public function getAutoPurgeMinimumInterval() {
		return $this->autoPurgeMinimumInterval;
	}


	public function getAutoPurgeCount() {
		return $this->autoPurgeCount;
	}


	public function getSimplePieCacheDuration() {
		return $this->simplePieCacheDuration;
	}


	public function getFeedFetcherTimeout() {
		return $this->feedFetcherTimeout;
	}


	public function getUseCronUpdates() {
		return $this->useCronUpdates;
	}


	public function setAutoPurgeMinimumInterval($value) {
		$this->autoPurgeMinimumInterval = $value;
	}


	public function setAutoPurgeCount($value) {
		$this->autoPurgeCount = $value;
	}


	public function setSimplePieCacheDuration($value) {
		$this->simplePieCacheDuration = $value;
	}


	public function setFeedFetcherTimeout($value) {
		$this->feedFetcherTimeout = $value;
	}


	public function setUseCronUpdates($value) {
		$this->useCronUpdates = $value;
	}


	public function setProxyPort($value) {
		$this->proxyPort = $value;
	}

	public function setProxyHost($value) {
		$this->proxyHost = $value;
	}

	public function setProxyAuth($value) {
		$this->proxyAuth = $value;
	}


	public function read($configPath, $createIfNotExists=false) {
		if($createIfNotExists && !$this->fileSystem->file_exists($configPath)) {

			$this->write($configPath);

		} else {

			$content = $this->fileSystem->file_get_contents($configPath);
			$configValues = parse_ini_string($content);

			if($configValues === false || count($configValues) === 0) {
				$this->api->log('Configuration invalid. Ignoring values.' , 'warn');
			} else {

				foreach($configValues as $key => $value) {
					if(property_exists($this, $key)) {
						$type = gettype($this->$key);
						settype($value, $type);
						$this->$key = $value;
					} else {
						$this->api->log('Configuration value "' . $key . 
							'" does not exist. Ignored value.' , 'warn');
					}
				}

			}
		}
	}


	public function write($configPath) {
		$ini = 
			"autoPurgeMinimumInterval = " . $this->autoPurgeMinimumInterval . "\n" .
			"autoPurgeCount = " . $this->autoPurgeCount . "\n" .
			"simplePieCacheDuration = " . $this->simplePieCacheDuration . "\n" .
			"feedFetcherTimeout = " . $this->feedFetcherTimeout . "\n" .
			"useCronUpdates = " . var_export($this->useCronUpdates, true) . "\n" .
			"proxyHost = " . $this->proxyHost . "\n" .
			"proxyPort = " . $this->proxyPort . "\n" .
			"proxyAuth = " . $this->proxyAuth;
		;

		$this->fileSystem->file_put_contents($configPath, $ini);
	}


}