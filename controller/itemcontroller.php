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

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Core\API;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;


class ItemController extends Controller {

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $api;

	public function __construct(API $api, IRequest $request, 
		                        FeedBusinessLayer $feedBusinessLayer,
		                        ItemBusinessLayer $itemBusinessLayer){
		parent::__construct($api->getAppName(), $request);
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->api = $api;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function items(){
		$userId = $this->api->getUserId();
		$showAll = $this->api->getUserValue('showAll') === '1';

		$limit = $this->params('limit');
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$offset = (int) $this->params('offset', 0);

		$this->api->setUserValue('lastViewedFeedId', $id);
		$this->api->setUserValue('lastViewedFeedType', $type);

		$params = array();

		try {

			// the offset is 0 if the user clicks on a new feed
			// we need to pass the newest feeds to not let the unread count get 
			// out of sync
			if($offset === 0) {
				$params['newestItemId'] = 
					$this->itemBusinessLayer->getNewestItemId($userId);
				$params['feeds'] = $this->feedBusinessLayer->findAll($userId);
				$params['starred'] = $this->itemBusinessLayer->starredCount($userId);
			}
						
			$params['items'] = $this->itemBusinessLayer->findAll($id, $type, $limit, 
				                                       $offset, $showAll, $userId);
		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(BusinessLayerException $ex) {}

		return new JSONResponse($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function newItems() {
		$userId = $this->api->getUserId();
		$showAll = $this->api->getUserValue('showAll') === '1';

		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$lastModified = (int) $this->params('lastModified', 0);

		$params = array();

		try {
			$params['newestItemId'] = $this->itemBusinessLayer->getNewestItemId($userId);
			$params['feeds'] = $this->feedBusinessLayer->findAll($userId);
			$params['starred'] = $this->itemBusinessLayer->starredCount($userId);			
			$params['items'] = $this->itemBusinessLayer->findAllNew($id, $type, 
				$lastModified, $showAll, $userId);
		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(BusinessLayerException $ex) {}

		return new JSONResponse($params);
	}


	private function setStarred($isStarred){
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');
		$guidHash = $this->params('guidHash');

		$this->itemBusinessLayer->star($feedId, $guidHash, $isStarred, $userId);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function star(){
		try {
			$this->setStarred(true);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function unstar(){
		try {
			$this->setStarred(false);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	private function setRead($isRead){
		$userId = $this->api->getUserId();
		$itemId = (int) $this->params('itemId');

		$this->itemBusinessLayer->read($itemId, $isRead, $userId);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function read(){
		try {
			$this->setRead(true);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function unread(){
		try {
			$this->setRead(false);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function readAll(){
		$userId = $this->api->getUserId();
		$highestItemId = (int) $this->params('highestItemId');

		$this->itemBusinessLayer->readAll($highestItemId, $userId);

		$params = array(
			'feeds' => $this->feedBusinessLayer->findAll($userId)
		);
		return new JSONResponse($params);
	}


}