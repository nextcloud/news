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

namespace OCA\News\API;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Core\API;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;


class FolderAPI extends Controller {

	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $api;

	public function __construct(API $api,
	                            IRequest $request,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer){
		parent::__construct($api->getAppName(), $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->api = $api;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function getAll() {
		$userId = $this->api->getUserId();
		$result = array(
			'folders' => array()
		);

		foreach ($this->folderBusinessLayer->findAll($userId) as $folder) {
			array_push($result['folders'], $folder->toAPI());
		}

		return new JSONResponse($result);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function create() {
		$userId = $this->api->getUserId();
		$folderName = $this->params('name');
		$result = array(
			'folders' => array()
		);

		try {
			$this->folderBusinessLayer->purgeDeleted($userId, false);
			$folder = $this->folderBusinessLayer->create($folderName, $userId);
			array_push($result['folders'], $folder->toAPI());

			return new JSONResponse($result);
		
		} catch(BusinessLayerValidationException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_UNPROCESSABLE_ENTITY);

		} catch(BusinessLayerConflictException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_CONFLICT);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function delete() {
		$userId = $this->api->getUserId();
		$folderId = (int) $this->params('folderId');

		try {
			$this->folderBusinessLayer->delete($folderId, $userId);
			return new JSONResponse();
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function update() {
		$userId = $this->api->getUserId();
		$folderId = (int) $this->params('folderId');
		$folderName = $this->params('name');

		try {
			$this->folderBusinessLayer->rename($folderId, $folderName, $userId);
			return new JSONResponse();

		} catch(BusinessLayerValidationException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_UNPROCESSABLE_ENTITY);

		} catch(BusinessLayerConflictException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_CONFLICT);

		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function read() {
		$userId = $this->api->getUserId();
		$folderId = (int) $this->params('folderId');
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readFolder($folderId, $newestItemId, $userId);
		return new JSONResponse();
	}


}
