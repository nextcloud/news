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
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FolderService;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Service\ServiceConflictException;
use \OCA\News\Service\ServiceValidationException;


class FolderController extends Controller {

	use JSONHttpError;

	private $folderService;
	private $feedService;
	private $itemService;
	private $userId;

	public function __construct($appName,
	                            IRequest $request,
	                            FolderService $folderService,
	                            FeedService $feedService,
	                            ItemService $itemService,
	                            $userId) {
		parent::__construct($appName, $request);
		$this->folderService = $folderService;
		$this->feedService = $feedService;
		$this->itemService = $itemService;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 */
	public function index() {
		$folders = $this->folderService->findAll($this->userId);
		return ['folders' => $folders];
	}


    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     * @param bool $open
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function open($folderId, $open) {
		try {
			$this->folderService->open($folderId, $open, $this->userId);
		} catch(ServiceNotFoundException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


    /**
     * @NoAdminRequired
     *
     * @param string $folderName
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function create($folderName) {
		try {
			// we need to purge deleted folders if a folder is created to
			// prevent already exists exceptions
			$this->folderService->purgeDeleted($this->userId, false);
			$folder = $this->folderService->create($folderName, $this->userId);

			return ['folders' => [$folder]];

		} catch(ServiceConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(ServiceValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		}

	}


    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function delete($folderId) {
		try {
			$this->folderService->markDeleted($folderId, $this->userId);
		} catch (ServiceNotFoundException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


    /**
     * @NoAdminRequired
     *
     * @param string $folderName
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function rename($folderName, $folderId) {
		try {
			$folder = $this->folderService->rename($folderId, $folderName,
				$this->userId);

			return ['folders' => [$folder]];

		} catch(ServiceConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(ServiceValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		} catch (ServiceNotFoundException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

	}

    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     * @param int $highestItemId
     * @return array
     */
	public function read($folderId, $highestItemId) {
		$this->itemService->readFolder($folderId, $highestItemId, $this->userId);

		return ['feeds' => $this->feedService->findAll($this->userId)];
	}


    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
	public function restore($folderId) {
		try {
			$this->folderService->unmarkDeleted($folderId, $this->userId);
		} catch (ServiceNotFoundException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}

        return [];
	}


}