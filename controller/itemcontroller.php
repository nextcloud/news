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

use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;


class ItemController extends Controller {

	use JSONHttpError;

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $userId;
	private $settings;

	public function __construct($appName, 
	                            IRequest $request, 
		                        FeedBusinessLayer $feedBusinessLayer,
		                        ItemBusinessLayer $itemBusinessLayer,
		                        IConfig $settings,
		                        $userId){
		parent::__construct($appName, $request);
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
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
					$this->itemBusinessLayer->getNewestItemId($this->userId);
				$params['feeds'] = $this->feedBusinessLayer->findAll($this->userId);
				$params['starred'] = $this->itemBusinessLayer->starredCount($this->userId);
			}
						
			$params['items'] = $this->itemBusinessLayer->findAll($id, $type, $limit, 
				                                       $offset, $showAll, $this->userId);
			
		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(BusinessLayerException $ex) {}

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
			$params['newestItemId'] = $this->itemBusinessLayer->getNewestItemId($this->userId);
			$params['feeds'] = $this->feedBusinessLayer->findAll($this->userId);
			$params['starred'] = $this->itemBusinessLayer->starredCount($this->userId);			
			$params['items'] = $this->itemBusinessLayer->findAllNew($id, $type, 
				$lastModified, $showAll, $this->userId);

		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(BusinessLayerException $ex) {}

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
			$this->itemBusinessLayer->star($feedId, $guidHash, true, $this->userId);
		} catch(BusinessLayerException $ex) {
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
			$this->itemBusinessLayer->star($feedId, $guidHash, false, $this->userId);
		} catch(BusinessLayerException $ex) {
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
			$this->itemBusinessLayer->read($itemId, true, $this->userId);
		} catch(BusinessLayerException $ex) {
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
			$this->itemBusinessLayer->read($itemId, false, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $highestItemId
	 */
	public function readAll($highestItemId){
		$this->itemBusinessLayer->readAll($highestItemId, $this->userId);
		return ['feeds' => $this->feedBusinessLayer->findAll($this->userId)];
	}


}