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

use \OCA\News\Service\FolderService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Service\ServiceConflictException;
use \OCA\News\Service\ServiceValidationException;


class FolderApiController extends ApiController {

	use JSONHttpError;

	private $folderService;
	private $itemService;
	private $userId;

	public function __construct($appName,
	                            IRequest $request,
	                            FolderService $folderService,
	                            ItemService $itemService,
	                            $userId){
		parent::__construct($appName, $request);
		$this->folderService = $folderService;
		$this->itemService = $itemService;
		$this->userId = $userId;
		$this->registerSerializer(new EntityApiSerializer('folders'));
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function index() {
		return $this->folderService->findAll($this->userId);
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
			$this->folderService->purgeDeleted($this->userId, false);
			return $this->folderService->create($name, $this->userId);
		} catch(ServiceValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		} catch(ServiceConflictException $ex) {
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
			$this->folderService->delete($folderId, $this->userId);
		} catch(ServiceNotFoundException $ex) {
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
			$this->folderService->rename($folderId, $name, $this->userId);

		} catch(ServiceValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		} catch(ServiceConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(ServiceNotFoundException $ex) {
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
		$this->itemService->readFolder($folderId, $newestItemId, $this->userId);
	}


}
