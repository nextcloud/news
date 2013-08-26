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

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;

use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\Db\FeedType;


class FeedController extends Controller {

	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $itemBusinessLayer;

	public function __construct(API $api, Request $request, 
		                        FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
		                        ItemBusinessLayer $itemBusinessLayer){
		parent::__construct($api, $request);
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function feeds(){
		$userId = $this->api->getUserId();

		// this method is also used to update the interface
		// because of this we also pass the starred count and the newest
		// item id which will be used for marking feeds read
		$params = array(
			'feeds' => $this->feedBusinessLayer->findAll($userId),
			'starred' => $this->itemBusinessLayer->starredCount($userId)
		);

		try {
			$params['newestItemId'] = 
				$this->itemBusinessLayer->getNewestItemId($userId);
		} catch (BusinessLayerException $ex) {}

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function active(){
		$userId = $this->api->getUserId();
		$feedId = (int) $this->api->getUserValue('lastViewedFeedId');
		$feedType = $this->api->getUserValue('lastViewedFeedType');
		
		// cast from null to int is 0
		if($feedType !== null){
			$feedType = (int) $feedType;
		}

		// check if feed or folder exists
		try {
			if($feedType === FeedType::FOLDER){
				$this->folderBusinessLayer->find($feedId, $userId);
			
			} elseif ($feedType === FeedType::FEED){
				$this->feedBusinessLayer->find($feedId, $userId);
			
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

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function create(){
		$url = $this->params('url');
		$parentFolderId = (int) $this->params('parentFolderId');
		$userId = $this->api->getUserId();

		try {
			// we need to purge deleted feeds if a feed is created to 
			// prevent already exists exceptions
			$this->feedBusinessLayer->purgeDeleted($userId, false);

			$feed = $this->feedBusinessLayer->create($url, $parentFolderId, $userId);
			$params = array(
				'feeds' => array($feed)
			);

			try {
				$params['newestItemId'] = 
					$this->itemBusinessLayer->getNewestItemId($userId);
			} catch (BusinessLayerException $ex) {}

			return $this->renderJSON($params);
		} catch(BusinessLayerException $ex) {
			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function delete(){
		$feedId = (int) $this->params('feedId');
		$userId = $this->api->getUserId();

		try {
			$this->feedBusinessLayer->markDeleted($feedId, $userId);
			return $this->renderJSON();
		} catch(BusinessLayerException $ex) {
			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function update(){
		try {
			$feedId = (int) $this->params('feedId');
			$userId = $this->api->getUserId();

			$feed = $this->feedBusinessLayer->update($feedId, $userId);

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

			return $this->renderJSON($params);

		} catch(BusinessLayerException $ex) {
			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function move(){
		$feedId = (int) $this->params('feedId');
		$parentFolderId = (int) $this->params('parentFolderId');
		$userId = $this->api->getUserId();

		try {
			$this->feedBusinessLayer->move($feedId, $parentFolderId, $userId);
			return $this->renderJSON();	
		} catch(BusinessLayerException $ex) {
			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function importGoogleReader() {
		$json = $this->params('json');
		$userId = $this->api->getUserId();

		$feed = $this->feedBusinessLayer->importGoogleReaderJSON($json, $userId);

		$params = array(
			'feeds' => array($feed)
		);
		return $this->renderJSON($params);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function read(){
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');
		$highestItemId = (int) $this->params('highestItemId');

		$this->itemBusinessLayer->readFeed($feedId, $highestItemId, $userId);

		$params = array(
			'feeds' => array(
				array(
					'id' => $feedId,
					'unreadCount' => 0
				)
			)
		);
		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function restore(){
		$feedId = (int) $this->params('feedId');
		$userId = $this->api->getUserId();

		try {
			$this->feedBusinessLayer->unmarkDeleted($feedId, $userId);
			return $this->renderJSON();
		} catch(BusinessLayerException $ex) {
			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


}