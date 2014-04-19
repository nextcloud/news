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

use \OCA\News\Core\Settings;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\Db\FeedType;


class FeedController extends Controller {

	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $userId;
	private $settings;

	public function __construct($appName, 
	                            IRequest $request, 
		                        FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
		                        ItemBusinessLayer $itemBusinessLayer,
		                        $userId,
		                        Settings $settings){
		parent::__construct($appName, $request);
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
		$this->settings = $settings;
	}


	/**
	 * @NoAdminRequired
	 */
	public function index(){

		// this method is also used to update the interface
		// because of this we also pass the starred count and the newest
		// item id which will be used for marking feeds read
		$params = array(
			'feeds' => $this->feedBusinessLayer->findAll($this->userId),
			'starred' => $this->itemBusinessLayer->starredCount($this->userId)
		);

		try {
			$params['newestItemId'] = 
				$this->itemBusinessLayer->getNewestItemId($this->userId);
		} catch (BusinessLayerException $ex) {}

		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function active(){
		$feedId = (int) $this->settings->getUserValue('lastViewedFeedId');
		$feedType = $this->settings->getUserValue('lastViewedFeedType');
		
		// cast from null to int is 0
		if($feedType !== null){
			$feedType = (int) $feedType;
		}

		// check if feed or folder exists
		try {
			if($feedType === FeedType::FOLDER){
				$this->folderBusinessLayer->find($feedId, $this->userId);
			
			} elseif ($feedType === FeedType::FEED){
				$this->feedBusinessLayer->find($feedId, $this->userId);
			
			// if its the first launch, those values will be null
			} elseif($feedType === null){
				throw new BusinessLayerException('');
			}
	
		} catch (BusinessLayerException $ex){
			$feedId = 0;
			$feedType = FeedType::SUBSCRIPTIONS;
		}

		$params = array(
			'activeFeed' => array(
				'id' => $feedId,
				'type' => $feedType
			)
		);

		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function create(){
		$url = $this->params('url');
		$parentFolderId = (int) $this->params('parentFolderId');

		try {
			// we need to purge deleted feeds if a feed is created to 
			// prevent already exists exceptions
			$this->feedBusinessLayer->purgeDeleted($this->userId, false);

			$feed = $this->feedBusinessLayer->create($url, $parentFolderId, $this->userId);
			$params = array(
				'feeds' => array($feed)
			);

			try {
				$params['newestItemId'] = 
					$this->itemBusinessLayer->getNewestItemId($this->userId);
			} catch (BusinessLayerException $ex) {}

			return new JSONResponse($params);

		} catch(BusinessLayerConflictException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_CONFLICT);
		
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_UNPROCESSABLE_ENTITY);
		}
	}


	/**
	 * @NoAdminRequired
	 */
	public function delete(){
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->markDeleted($feedId, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 */
	public function update(){
		try {
			$feedId = (int) $this->params('feedId');

			$feed = $this->feedBusinessLayer->update($feedId, $this->userId);

			$params = array(
				'feeds' => array(
					// only pass unreadcount to not accidentally readd
					// the feed again
					array(
						'id' => $feed->getId(),
						'unreadCount' => $feed->getUnreadCount()
					)
				)
			);

			return new JSONResponse($params);

		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 */
	public function move(){
		$feedId = (int) $this->params('feedId');
		$parentFolderId = (int) $this->params('parentFolderId');

		try {
			$this->feedBusinessLayer->move($feedId, $parentFolderId, $this->userId);
			return new JSONResponse();	
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function rename() {
		$feedId = (int) $this->params('feedId');
		$feedTitle = $this->params('feedTitle');

		try {
			$this->feedBusinessLayer->rename($feedId, $feedTitle, $this->userId);
			return new JSONResponse();	
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function import() {
		$json = $this->params('json');

		$feed = $this->feedBusinessLayer->importArticles($json, $this->userId);

		$params = array();
		if($feed) {
			$params['feeds'] = array($feed);
		}

		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function read(){
		$feedId = (int) $this->params('feedId');
		$highestItemId = (int) $this->params('highestItemId');

		$this->itemBusinessLayer->readFeed($feedId, $highestItemId, $this->userId);

		$params = array(
			'feeds' => array(
				array(
					'id' => $feedId,
					'unreadCount' => 0
				)
			)
		);
		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function restore(){
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->unmarkDeleted($feedId, $this->userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


}