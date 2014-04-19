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

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;


class FolderApiController extends Controller {

	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $userId;

	public function __construct($appName,
	                            IRequest $request,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            $userId){
		parent::__construct($appName, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function index() {
		$result = array(
			'folders' => array()
		);

		foreach ($this->folderBusinessLayer->findAll($this->userId) as $folder) {
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
		$folderName = $this->params('name');
		$result = array(
			'folders' => array()
		);

		try {
			$this->folderBusinessLayer->purgeDeleted($this->userId, false);
			$folder = $this->folderBusinessLayer->create($folderName, $this->userId);
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
		$folderId = (int) $this->params('folderId');

		try {
			$this->folderBusinessLayer->delete($folderId, $this->userId);
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
		$folderId = (int) $this->params('folderId');
		$folderName = $this->params('name');

		try {
			$this->folderBusinessLayer->rename($folderId, $folderName, $this->userId);
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
		$folderId = (int) $this->params('folderId');
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readFolder($folderId, $newestItemId, $this->userId);
		return new JSONResponse();
	}


}
