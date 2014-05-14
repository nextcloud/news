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
use \OCP\ILogger;
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http;

use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;


class FeedApiController extends ApiController {

	use JSONHttpError;

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $userId;
	private $logger;
	private $loggerParams;

	public function __construct($appName,
	                            IRequest $request,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            ILogger $logger,
	                            $userId,
	                            $loggerParams){
		parent::__construct($appName, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
		$this->logger = $logger;
		$this->loggerParams = $loggerParams;
		$this->registerSerializer(new EntityApiSerializer('feeds'));
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function index() {

		$result = array(
			'feeds' => array(),
			'starredCount' => $this->itemBusinessLayer->starredCount($this->userId),
			'feeds' => $this->feedBusinessLayer->findAll($this->userId)
		);

		
		try {
			$result['newestItemId'] = $this->itemBusinessLayer->getNewestItemId($this->userId);
		
		// in case there are no items, ignore
		} catch(BusinessLayerException $ex) {}

		return $result;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param string $url
	 * @param int $folderId
	 */
	public function create($url, $folderId=0) {
		try {
			$this->feedBusinessLayer->purgeDeleted($this->userId, false);

			$feed = $this->feedBusinessLayer->create($url, $folderId, $this->userId);
			$result = array(
				'feeds' => array($feed)
			);

			try {
				$result['newestItemId'] = $this->itemBusinessLayer->getNewestItemId($this->userId);

			// in case there are no items, ignore
			} catch(BusinessLayerException $ex) {}

			return $result;

		} catch(BusinessLayerConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * 
	 * @param int $feedId
	 */
	public function delete($feedId) {
		try {
			$this->feedBusinessLayer->delete($feedId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $feedId
	 * @param int $newestItemId
	 */
	public function read($feedId, $newestItemId) {
		$this->itemBusinessLayer->readFeed($feedId, $newestItemId, $this->userId);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $feedId
	 * @param int $folderId
	 */
	public function move($feedId, $folderId) {
		try {
			$this->feedBusinessLayer->move($feedId, $folderId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $feedId
	 * @param string $feedTitle
	 */
	public function rename($feedId, $feedTitle) {
		try {
			$this->feedBusinessLayer->rename($feedId, $feedTitle, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function fromAllUsers() {
		$feeds = $this->feedBusinessLayer->findAllFromAllUsers();
		$result = array('feeds' => array());

		foreach ($feeds as $feed) {
			$result['feeds'][] = array(
				'id' => $feed->getId(), 
				'userId' => $feed->getUserId()
			);
		}

		return $result;
	}


	/**
	 * @NoCSRFRequired
	 *
	 * @param string $userId
	 * @param int $feedId
	 */
	public function update($userId, $feedId) {
		try {
			$this->feedBusinessLayer->update($feedId, $userId);
		// ignore update failure (feed could not be reachable etc, we dont care)
		} catch(\Exception $ex) {
			$this->logger->debug('Could not update feed ' . $ex->getMessage(),
					$this->loggerParams);
		}
	}


}
