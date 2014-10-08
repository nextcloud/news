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

use \OCA\News\Service\ItemService;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\FolderService;
use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Service\ServiceConflictException;
use \OCA\News\Db\FeedType;


class FeedController extends Controller {

	use JSONHttpError;

	private $feedService;
	private $folderService;
	private $itemService;
	private $userId;
	private $settings;

	public function __construct($appName,
	                            IRequest $request,
		                        FolderService $folderService,
	                            FeedService $feedService,
		                        ItemService $itemService,
		                        IConfig $settings,
	                            $userId){
		parent::__construct($appName, $request);
		$this->feedService = $feedService;
		$this->folderService = $folderService;
		$this->itemService = $itemService;
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
			'feeds' => $this->feedService->findAll($this->userId),
			'starred' => $this->itemService->starredCount($this->userId)
		];

		try {
			$params['newestItemId'] =
				$this->itemService->getNewestItemId($this->userId);

		// An exception occurs if there is a newest item. If there is none,
		// simply ignore it and do not add the newestItemId
		} catch (ServiceNotFoundException $ex) {}

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
				$this->folderService->find($feedId, $this->userId);

			} elseif ($feedType === FeedType::FEED){
				$this->feedService->find($feedId, $this->userId);

			// if its the first launch, those values will be null
			} elseif($feedType === null){
				throw new ServiceNotFoundException('');
			}

		} catch (ServiceNotFoundException $ex){
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
     * @param string $title
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function create($url, $parentFolderId, $title){
		try {
			// we need to purge deleted feeds if a feed is created to
			// prevent already exists exceptions
			$this->feedService->purgeDeleted($this->userId, false);

			$feed = $this->feedService->create($url, $parentFolderId,
				                               $this->userId, $title);
			$params = ['feeds' => [$feed]];

			try {
				$params['newestItemId'] =
					$this->itemService->getNewestItemId($this->userId);

			// An exception occurs if there is a newest item. If there is none,
			// simply ignore it and do not add the newestItemId
			} catch (ServiceNotFoundException $ex) {}

			return $params;

		} catch(ServiceConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		}

	}


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function delete($feedId){
		try {
			$this->feedService->markDeleted($feedId, $this->userId);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function update($feedId){
		try {
			$feed = $this->feedService->update($feedId, $this->userId);

			return [
				'feeds' => [
					// only pass unread count to not accidentally readd
					// the feed again
					[
						'id' => $feed->getId(),
						'unreadCount' => $feed->getUnreadCount()
					]
				]
			];

		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

	}


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     * @param int $parentFolderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function move($feedId, $parentFolderId){
		try {
			$this->feedService->move($feedId, $parentFolderId, $this->userId);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}

    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     * @param string $feedTitle
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function rename($feedId, $feedTitle) {
		try {
			$this->feedService->rename($feedId, $feedTitle, $this->userId);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


    /**
     * @NoAdminRequired
     *
     * @param array $json
     * @return array
     */
	public function import($json) {
		$feed = $this->feedService->importArticles($json, $this->userId);

		$params = [
			'starred' => $this->itemService->starredCount($this->userId)
		];

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
     * @return array
     */
	public function read($feedId, $highestItemId){
		$this->itemService->readFeed($feedId, $highestItemId, $this->userId);

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
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function restore($feedId){
		try {
			$this->feedService->unmarkDeleted($feedId, $this->userId);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


}