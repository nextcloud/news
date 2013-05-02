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

namespace OCA\News\External;

use \OCA\AppFramework\Core\API;

use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;


class FeedAPI {

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $api;

	public function __construct(API $api,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer){
		$this->api = $api;
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
	}


	public function getAll(array $urlParams=array()) {

		$userId = $this->api->getUserId();

		$result = array(
			'feeds' => array(),
			'starredCount' => $this->itemBusinessLayer->starredCount($userId)
		);

		foreach ($this->feedBusinessLayer->findAll($userId) as $feed) {
			array_push($result['feeds'], $feed->toAPI());
		}

		// check case when there are no items
		try {
			$result['newestItemId'] = 
				$this->itemBusinessLayer->getNewestItemId($userId);
		} catch(BusinessLayerException $ex) {}

		return $result;
	}


}
