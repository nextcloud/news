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
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;


class FolderApiController extends ApiController {

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
	 * @CORS
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
	 * @CORS
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
	 * @CORS
	 */
	public function delete() {
		$folderId = (int) $this->params('folderId');

		try {
			$this->folderBusinessLayer->delete($folderId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return new JSONResponse(array('message' => $ex->getMessage()),
				Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function update() {
		$folderId = (int) $this->params('folderId');
		$folderName = $this->params('name');

		try {
			$this->folderBusinessLayer->rename($folderId, $folderName, $this->userId);

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
	 * @CORS
	 */
	public function read() {
		$folderId = (int) $this->params('folderId');
		$newestItemId = (int) $this->params('newestItemId');

		$this->itemBusinessLayer->readFolder($folderId, $newestItemId, $this->userId);
	}


}
