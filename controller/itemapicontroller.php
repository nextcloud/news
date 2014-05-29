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

use \OCA\News\Service\ItemService;
use \OCA\News\Service\ServiceNotFoundException;

class ItemApiController extends ApiController {

	use JSONHttpError;

	private $itemService;
	private $userId;
	private $serializer;

	public function __construct($appName,
	                            IRequest $request,
	                            ItemService $itemService,
	                            $userId){
		parent::__construct($appName, $request);
		$this->itemService = $itemService;
		$this->userId = $userId;
		$this->serializer = new EntityApiSerializer('items');
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
	 * @param int $oldestFirst
	 */
	public function index($type, $id, $getRead, $batchSize=20, $offset=0,
	                      $oldestFirst=false) {
		return $this->serializer->serialize(
			$this->itemService->findAll(
				$id, $type, $batchSize, $offset, $getRead, $oldestFirst,
				$this->userId
			)
		);
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
		return $this->serializer->serialize(
			$this->itemService->findAllNew($id, $type, $lastModified,
		                                   true, $this->userId)
		);
	}


	private function setRead($isRead, $itemId) {
		try {
			$this->itemService->read($itemId, $isRead, $this->userId);
		} catch(ServiceNotFoundException $ex){
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


	private function setStarred($isStarred, $feedId, $guidHash) {
		try {
			$this->itemService->star($feedId, $guidHash, $isStarred, $this->userId);
		} catch(ServiceNotFoundException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
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
		$this->itemService->readAll($newestItemId, $this->userId);
	}


	private function setMultipleRead($isRead, $items) {
		foreach($items as $id) {
			try {
				$this->itemService->read($id, $isRead, $this->userId);
			} catch(ServiceNotFoundException $ex) {
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
				$this->itemService->star($item['feedId'], $item['guidHash'],
					                           $isStarred, $this->userId);
			} catch(ServiceNotFoundException $ex) {
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
