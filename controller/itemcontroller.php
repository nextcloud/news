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

use \OCA\News\Core\Settings;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;


class ItemController extends Controller {

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $userId;
	private $settings;

	public function __construct($appName, 
	                            IRequest $request, 
		                        FeedBusinessLayer $feedBusinessLayer,
		                        ItemBusinessLayer $itemBusinessLayer,
		                        $userId,
		                        Settings $settings){
		parent::__construct($appName, $request);
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->userId = $userId;
		$this->settings = $settings;
	}


	/**
	 * @NoAdminRequired
	 */
	public function index(){
		$showAll = $this->settings->getUserValue('showAll') === '1';

		$limit = $this->params('limit');
		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$offset = (int) $this->params('offset', 0);

		$this->settings->setUserValue('lastViewedFeedId', $id);
		$this->settings->setUserValue('lastViewedFeedType', $type);

		$params = array();

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

		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function newItems() {
		$showAll = $this->settings->getUserValue('showAll') === '1';

		$type = (int) $this->params('type');
		$id = (int) $this->params('id');
		$lastModified = (int) $this->params('lastModified', 0);

		$params = array();

		try {
			$params['newestItemId'] = $this->itemBusinessLayer->getNewestItemId($this->userId);
			$params['feeds'] = $this->feedBusinessLayer->findAll($this->userId);
			$params['starred'] = $this->itemBusinessLayer->starredCount($this->userId);			
			$params['items'] = $this->itemBusinessLayer->findAllNew($id, $type, 
				$lastModified, $showAll, $this->userId);
		// this gets thrown if there are no items
		// in that case just return an empty array
		} catch(BusinessLayerException $ex) {}

		return new JSONResponse($params);
	}


	private function setStarred($isStarred){
		$feedId = (int) $this->params('feedId');
		$guidHash = $this->params('guidHash');

		$this->itemBusinessLayer->star($feedId, $guidHash, $isStarred, $this->userId);
	}


	/**
	 * @NoAdminRequired
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
	 * @NoAdminRequired
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
		$itemId = (int) $this->params('itemId');

		$this->itemBusinessLayer->read($itemId, $isRead, $this->userId);
	}


	/**
	 * @NoAdminRequired
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
	 * @NoAdminRequired
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
	 * @NoAdminRequired
	 */
	public function readAll(){
		$highestItemId = (int) $this->params('highestItemId');

		$this->itemBusinessLayer->readAll($highestItemId, $this->userId);

		$params = array(
			'feeds' => $this->feedBusinessLayer->findAll($this->userId)
		);
		return new JSONResponse($params);
	}


}