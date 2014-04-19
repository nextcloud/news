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
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Core\Logger;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;


class FeedApiController extends Controller {

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $userId;
	private $logger;

	public function __construct($appName,
	                            IRequest $request,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            Logger $logger,
	                            $userId){
		parent::__construct($appName, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
		$this->logger = $logger;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function index() {

		$result = array(
			'feeds' => array(),
			'starredCount' => $this->itemBusinessLayer->starredCount($this->userId)
		);

		foreach ($this->feedBusinessLayer->findAll($this->userId) as $feed) {
			array_push($result['feeds'], $feed->toAPI());
		}

		// check case when there are no items
		try {
			$result['newestItemId'] =
				$this->itemBusinessLayer->getNewestItemId($this->userId);
		} catch(BusinessLayerException $ex) {}

		return new JSONResponse($result);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function create() {
		$feedUrl = $this->params('url');
		$folderId = (int) $this->params('folderId', 0);

		try {
			$this->feedBusinessLayer->purgeDeleted($this->userId, false);

			$feed = $this->feedBusinessLayer->create($feedUrl, $folderId, $this->userId);
			$result = array(
				'feeds' => array($feed->toAPI())
			);

			try {
				$result['newestItemId'] =
					$this->itemBusinessLayer->getNewestItemId($this->userId);
			} catch(BusinessLayerException $ex) {}

			return new JSONResponse($result);

		} catch(BusinessLayerConflictException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_CONFLICT);
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function delete() {
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->delete($feedId, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
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
		$feedId = (int) $this->params('feedId');
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readFeed($feedId, $newestItemId, $this->userId);
		return new JSONResponse();
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function move() {
		$feedId = (int) $this->params('feedId');
		$folderId = (int) $this->params('folderId');

		try {
			$this->feedBusinessLayer->move($feedId, $folderId, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function rename() {
		$feedId = (int) $this->params('feedId');
		$feedTitle = $this->params('feedTitle');

		try {
			$this->feedBusinessLayer->rename($feedId, $feedTitle, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoCSRFRequired
	 * @API
	 */
	public function fromAllUsers() {
		$feeds = $this->feedBusinessLayer->findAllFromAllUsers();
		$result = array('feeds' => array());

		foreach ($feeds as $feed) {
			array_push($result['feeds'], array(
				'id' => $feed->getId(),
				'userId' => $feed->getUserId()
			));
		}

		return new JSONResponse($result);
	}


	/**
	 * @NoCSRFRequired
	 * @API
	 */
	public function update() {
		$userId = $this->params('userId');
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->update($feedId, $userId);
		// ignore update failure (feed could not be reachable etc, we dont care)
		} catch(\Exception $ex) {
			$this->logger->log('Could not update feed ' . $ex->getMessage(),
					'debug');
		}
		return new JSONResponse();

	}


}
