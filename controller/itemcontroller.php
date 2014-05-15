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

use \OCA\News\Service\ServiceException;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\FeedService;


class ItemController extends Controller {

	use JSONHttpError;

	private $itemService;
	private $feedService;
	private $userId;
	private $settings;

	public function __construct($appName, 
	                            IRequest $request, 
		                        FeedService $feedService,
		                        ItemService $itemService,
		                        IConfig $settings,
		                        $userId){
		parent::__construct($appName, $request);
		$this->itemService = $itemService;
		$this->feedService = $feedService;
		$this->userId = $userId;
		$this->settings = $settings;
	}


	/**
	 * @NoAdminRequired
	 *
 	 * @param int $type
	 * @param int $id
	 * @param int $limit
	 * @param int $offset
	 */
	public function index($type, $id, $limit, $offset=0) {
		$showAll = $this->settings->getUserValue($this->userId, $this->appName,
			'showAll') === '1';
		$oldestFirst = $this->settings->getUserValue($this->userId, $this->appName,
			'oldestFirst') === '1';

		$this->settings->setUserValue($this->userId, $this->appName,
			'lastViewedFeedId', $id);
		$this->settings->setUserValue($this->userId, $this->appName,
			'lastViewedFeedType', $type);

		$params = [];

		try {

			// the offset is 0 if the user clicks on a new feed
			// we need to pass the newest feeds to not let the unread count get 
			// out of sync
			if($offset === 0) {
				$params['newestItemId'] = 
					$this->itemService->getNewestItemId($this->userId);
				$params['feeds'] = $this->feedService->findAll($this->userId);
				$params['starred'] = $this->itemService->starredCount($this->userId);
			}
						
			$params['items'] = $this->itemService->findAll(
				$id, $type, $limit, $offset, $showAll, $this->userId, $oldestFirst
			);
			
		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(ServiceException $ex) {}

		return $params;
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $type
	 * @param int $id
	 * @param int $lastModified
	 */
	public function newItems($type, $id, $lastModified=0) {
		$showAll = $this->settings->getUserValue($this->userId, $this->appName,			
			'showAll') === '1';

		$params = [];

		try {
			$params['newestItemId'] = $this->itemService->getNewestItemId($this->userId);
			$params['feeds'] = $this->feedService->findAll($this->userId);
			$params['starred'] = $this->itemService->starredCount($this->userId);			
			$params['items'] = $this->itemService->findAllNew($id, $type, 
				$lastModified, $showAll, $this->userId);

		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(ServiceException $ex) {}

		return $params;
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 * @param string $guidHash
	 */
	public function star($feedId, $guidHash){
		try {
			$this->itemService->star($feedId, $guidHash, true, $this->userId);
		} catch(ServiceException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $feedId
	 * @param string $guidHash
	 */
	public function unstar($feedId, $guidHash){
		try {
			$this->itemService->star($feedId, $guidHash, false, $this->userId);
		} catch(ServiceException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $itemId
	 */
	public function read($itemId){
		try {
			$this->itemService->read($itemId, true, $this->userId);
		} catch(ServiceException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $itemId
	 */
	public function unread($itemId){
		try {
			$this->itemService->read($itemId, false, $this->userId);
		} catch(ServiceException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $highestItemId
	 */
	public function readAll($highestItemId){
		$this->itemService->readAll($highestItemId, $this->userId);
		return ['feeds' => $this->feedService->findAll($this->userId)];
	}


}