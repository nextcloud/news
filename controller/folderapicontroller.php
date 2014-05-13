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

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;


class FolderApiController extends ApiController {

	use JSONHttpError;

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
		$this->registerSerializer(new EntityApiSerializer('folders'));

		return $this->folderBusinessLayer->findAll($this->userId);
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param string $name
	 */
	public function create($name) {
		try {
			$this->folderBusinessLayer->purgeDeleted($this->userId, false);
			$folder = $this->folderBusinessLayer->create($folderName, $this->userId);
			
			$this->registerSerializer(new EntityApiSerializer('folders'));
			return $folder;

		} catch(BusinessLayerValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		} catch(BusinessLayerConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $folderId
	 */
	public function delete($folderId) {
		try {
			$this->folderBusinessLayer->delete($folderId, $this->userId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param int $folderId
	 * @param string $name
	 */
	public function update($folderId, $name) {
		try {
			$this->folderBusinessLayer->rename($folderId, $folderName, $this->userId);

		} catch(BusinessLayerValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		} catch(BusinessLayerConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @param int $folderId
	 * @param int $newestItemId
	 */
	public function read($folderId, $newestItemId) {
		$this->itemBusinessLayer->readFolder($folderId, $newestItemId, $this->userId);
	}


}
