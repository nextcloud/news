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
use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\Request;

use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;


class ItemAPI extends Controller {

	private $itemBusinessLayer;

	public function __construct(API $api,
	                            Request $request,
	                            ItemBusinessLayer $itemBusinessLayer){
		parent::__construct($api, $request);
		$this->itemBusinessLayer = $itemBusinessLayer;
	}


	public function getAll() {
		$result = array(
			'items' => array()
		);

		$userId = $this->api->getUserId();
		$batchSize = (int) $this->params('batchSize');
		$offset = (int) $this->params('offset', 0);
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$showAll = $this->params('getRead') === 'true';

		$items = $this->itemBusinessLayer->findAll(
			$id,
			$type,
			$batchSize,
			$offset,
			$showAll,
			$userId
		);

		foreach ($items as $item) {
			array_push($result['items'], $item->toAPI());
		}

		return new NewsAPIResult($result);
	}


	public function getUpdated() {
		$result = array(
			'items' => array()
		);

		$userId = $this->api->getUserId();
		$lastModified = (int) $this->params('lastModified', 0);
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');

		$items = $this->itemBusinessLayer->findAllNew(
			$id,
			$type,
			$lastModified,
			true,
			$userId
		);

		foreach ($items as $item) {
			array_push($result['items'], $item->toAPI());
		}

		return new NewsAPIResult($result);
	}


	private function setRead($isRead) {
		$userId = $this->api->getUserId();
		$itemId = $this->params('itemId');
		$this->itemBusinessLayer->read($itemId, $isRead, $userId);
	}


	public function read() {
		$this->setRead(true);
		return new NewsAPIResult();
	}


	public function unread() {

	}


	public function star() {

	}


	public function unstar() {

	}


}
