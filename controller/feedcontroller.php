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
use \OCP\IConfig;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;

use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\Db\FeedType;


class FeedController extends Controller {

	use JSONHttpError;

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
		                        IConfig $settings,
	                            $userId){
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
		$params = [
			'feeds' => $this->feedBusinessLayer->findAll($this->userId),
			'starred' => $this->itemBusinessLayer->starredCount($this->userId)
		];

		try {
			$params['newestItemId'] = 
				$this->itemBusinessLayer->getNewestItemId($this->userId);
		
		// An exception occurs if there is a newest item. If there is none,
		// simply ignore it and do not add the newestItemId
		} catch (BusinessLayerException $ex) {}

		return $params;
	}


	/**
	 * @NoAdminRequired
	 */
	public function active(){
		$feedId = (int) $this->settings->getUserValue($this->userId, 
			$this->appName,'lastViewedFeedId');
		$feedType = $this->settings->getUserValue($this->userId, $this->appName,
			'lastViewedFeedType');
		
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

		return [
			'activeFeed' => [
				'id' => $feedId,
				'type' => $feedType
			]
		];
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param string $url
	 * @param int $parentFolderId
	 */
	public function create($url, $parentFolderId){
		try {
			// we need to purge deleted feeds if a feed is created to 
			// prevent already exists exceptions
			$this->feedBusinessLayer->purgeDeleted($this->userId, false);

			$feed = $this->feedBusinessLayer->create($url, $parentFolderId, $this->userId);
			$params = ['feeds' => [$feed]];

			try {
				$params['newestItemId'] = 
					$this->itemBusinessLayer->getNewestItemId($this->userId);

			// An exception occurs if there is a newest item. If there is none,
			// simply ignore it and do not add the newestItemId
			} catch (BusinessLayerException $ex) {}

			return $params;

		} catch(BusinessLayerConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		}
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $feedId
	 */
	public function delete($feedId){
		try {
			$this->feedBusinessLayer->markDeleted($feedId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 */
	public function update($feedId){
		try {
			$feed = $this->feedBusinessLayer->update($feedId, $this->userId);

			return [
				'feeds' => [
					// only pass unreadcount to not accidentally readd
					// the feed again
					[
						'id' => $feed->getId(),
						'unreadCount' => $feed->getUnreadCount()
					]
				]
			];

		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 * @param int $parentFolderId
	 */
	public function move($feedId, $parentFolderId){
		try {
			$this->feedBusinessLayer->move($feedId, $parentFolderId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
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
	 * @NoAdminRequired
	 *
	 * @param array $json
	 */
	public function import($json) {
		$feed = $this->feedBusinessLayer->importArticles($json, $this->userId);

		$params = [];

		if($feed) {
			$params['feeds'] = [$feed];
		}

		return $params;
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 * @param int $highestItemId
	 */
	public function read($feedId, $highestItemId){
		$this->itemBusinessLayer->readFeed($feedId, $highestItemId, $this->userId);

		return [
			'feeds' => [
				[
					'id' => $feedId,
					'unreadCount' => 0
				]
			]
		];
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 */
	public function restore($feedId){
		try {
			$this->feedBusinessLayer->unmarkDeleted($feedId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


}