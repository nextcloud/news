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

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;

class ItemApiController extends ApiController {

	use JSONHttpError;

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
	 * @CORS
	 * 
	 * @param int $type
	 * @param int $id
	 * @param bool $getRead
	 * @param int $batchSize
	 * @param int $offset
	 */
	public function index($type, $id, $getRead, $batchSize=20, $offset=0) {
		$result = array(
			'items' => array()
		);

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

		return $result;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * 
	 * @param int $type
	 * @param int $id
	 * @param int $lastModified
	 */
	public function updated($type, $id, $lastModified=0) {
		$result = array(
			'items' => array()
		);

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

		$result;
	}


	private function setRead($isRead, $itemId) {
		try {
			$this->itemBusinessLayer->read($itemId, $isRead, $this->userId);
		} catch(BusinessLayerException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	private function setStarred($isStarred, $feedId, $guidHash) {
		try {
			$this->itemBusinessLayer->star($feedId, $guidHash, $isStarred, $this->userId);
		} catch(BusinessLayerException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $itemId
	 */
	public function read($itemId) {
		return $this->setRead(true, $itemId);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $itemId
	 */
	public function unread($itemId) {
		return $this->setRead(false, $itemId);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $feedId
	 * @param string $guidHash
	 */
	public function star($feedId, $guidHash) {
		return $this->setStarred(true, $feedId, $guidHash);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $feedId
	 * @param string $guidHash
	 */
	public function unstar($feedId, $guidHash) {
		return $this->setStarred(false, $feedId, $guidHash);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $newestItemId
	 */
	public function readAll($newestItemId) {
		$this->itemBusinessLayer->readAll($newestItemId, $this->userId);
	}


	private function setMultipleRead($isRead, $items) {
		foreach($items as $id) {
			try {
				$this->itemBusinessLayer->read($id, $isRead, $this->userId);
			} catch(BusinessLayerException $ex) {
				continue;
			}
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int[] item ids
	 */
	public function readMultiple($items) {
		return $this->setMultipleRead(true, $items);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int[] item ids
	 */
	public function unreadMultiple($items) {
		return $this->setMultipleRead(false, $items);
	}


	private function setMultipleStarred($isStarred, $items) {
		foreach($items as $item) {
			try {
				$this->itemBusinessLayer->star($item['feedId'],
					$item['guidHash'], $isStarred, $this->userId);
			} catch(BusinessLayerException $ex) {
				continue;
			}
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int[] item ids
	 */
	public function starMultiple($items) {
		return $this->setMultipleStarred(true, $items);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * 
	 * @param int[] item ids
	 */
	public function unstarMultiple($items) {
		return $this->setMultipleStarred(false, $items);
	}


}
