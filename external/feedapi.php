<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

namespace OCA\News\External;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\Http;

use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerExistsException;


class FeedAPI extends Controller {

	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $folderBusinessLayer;

	public function __construct(API $api,
	                            Request $request,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer){
		parent::__construct($api, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function getAll() {
		$userId = $this->api->getUserId();

		$result = array(
			'feeds' => array(),
			'starredCount' => $this->itemBusinessLayer->starredCount($userId)
		);

		foreach ($this->feedBusinessLayer->findAll($userId) as $feed) {
			array_push($result['feeds'], $feed->toAPI());
		}

		// check case when there are no items
		try {
			$result['newestItemId'] =
				$this->itemBusinessLayer->getNewestItemId($userId);
		} catch(BusinessLayerException $ex) {}

		return new JSONResponse($result);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function create() {
		$userId = $this->api->getUserId();
		$feedUrl = $this->params('url');
		$folderId = (int) $this->params('folderId', 0);

		try {
			$this->feedBusinessLayer->purgeDeleted($userId, false);

			$feed = $this->feedBusinessLayer->create($feedUrl, $folderId, $userId);
			$result = array(
				'feeds' => array($feed->toAPI())
			);

			try {
				$result['newestItemId'] =
					$this->itemBusinessLayer->getNewestItemId($userId);
			} catch(BusinessLayerException $ex) {}

			return new JSONResponse($result);

		} catch(BusinessLayerExistsException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_CONFLICT);
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function delete() {
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->delete($feedId, $userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function read() {
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readFeed($feedId, $newestItemId, $userId);
		return new JSONResponse();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function move() {
		$userId = $this->api->getUserId();
		$feedId = (int) $this->params('feedId');
		$folderId = (int) $this->params('folderId');

		try {
			$this->feedBusinessLayer->move($feedId, $folderId, $userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function getAllFromAllUsers() {
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
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function update() {
		$userId = $this->params('userId');
		$feedId = (int) $this->params('feedId');

		try {
			$this->feedBusinessLayer->update($feedId, $userId);
		// ignore update failure (feed could not be reachable etc, we dont care)
		} catch(BusinessLayerException $ex) {}
		return new JSONResponse();

	}


}
