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

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;

class ItemApiController extends Controller {

	private $itemBusinessLayer;
	private $userId;

	public function __construct($appName,
	                            IRequest $request,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            $userId){
		parent::__construct($appName, $request);
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function index() {
		$result = array(
			'items' => array()
		);

		$batchSize = (int) $this->params('batchSize', 20);
		$offset = (int) $this->params('offset', 0);
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$showAll = $this->params('getRead');

		if($showAll === 'true' || $showAll === true) {
			$showAll = true;
		} else {
			$showAll = false;
		}

		$items = $this->itemBusinessLayer->findAll(
			$id,
			$type,
			$batchSize,
			$offset,
			$showAll,
			$this->userId
		);

		foreach ($items as $item) {
			array_push($result['items'], $item->toAPI());
		}

		return new JSONResponse($result);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function updated() {
		$result = array(
			'items' => array()
		);

		$lastModified = (int) $this->params('lastModified', 0);
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');

		$items = $this->itemBusinessLayer->findAllNew(
			$id,
			$type,
			$lastModified,
			true,
			$this->userId
		);

		foreach ($items as $item) {
			array_push($result['items'], $item->toAPI());
		}

		return new JSONResponse($result);
	}


	private function setRead($isRead) {
		$itemId = (int) $this->params('itemId');
		try {
			$this->itemBusinessLayer->read($itemId, $isRead, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex){
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	private function setStarred($isStarred) {
		$feedId = (int) $this->params('feedId');
		$guidHash = $this->params('guidHash');
		try {
			$this->itemBusinessLayer->star($feedId, $guidHash, $isStarred, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex){
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function read() {
		return $this->setRead(true);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function unread() {
		return $this->setRead(false);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function star() {
		return $this->setStarred(true);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function unstar() {
		return $this->setStarred(false);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function readAll() {
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readAll($newestItemId, $this->userId);
		return new JSONResponse();
	}


	private function setMultipleRead($isRead) {
		$items = $this->params('items');

		foreach($items as $id) {
			try {
				$this->itemBusinessLayer->read($id, $isRead, $this->userId);
			} catch(BusinessLayerException $ex) {
				continue;
			}
		}

		return new JSONResponse();
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function readMultiple() {
		return $this->setMultipleRead(true);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function unreadMultiple() {
		return $this->setMultipleRead(false);
	}


	private function setMultipleStarred($isStarred) {
		$items = $this->params('items');

		foreach($items as $item) {
			try {
				$this->itemBusinessLayer->star($item['feedId'],
					$item['guidHash'], $isStarred, $this->userId);
			} catch(BusinessLayerException $ex) {
				continue;
			}
		}

		return new JSONResponse();
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function starMultiple() {
		return $this->setMultipleStarred(true);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function unstarMultiple() {
		return $this->setMultipleStarred(false);
	}


}
