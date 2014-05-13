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
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;

class FolderController extends Controller {

	use JSONHttpError;

	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $userId;

	public function __construct($appName, 
	                            IRequest $request, 
	                            FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            $userId){
		parent::__construct($appName, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 */
	public function index(){
		$folders = $this->folderBusinessLayer->findAll($this->userId);
		$result = array(
			'folders' => $folders
		);
		return $result;
	}


	private function setOpened($isOpened, $folderId){
		$this->folderBusinessLayer->open($folderId, $isOpened, $this->userId);
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $folderId
	 */
	public function open($folderId){
		try {
			$this->setOpened(true, $folderId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $folderId
	 */
	public function collapse($folderId){
		try {
			$this->setOpened(false, $folderId);
		} catch(BusinessLayerException $ex) {
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param string $folderName
	 */
	public function create($folderName){
		try {
			// we need to purge deleted folders if a folder is created to 
			// prevent already exists exceptions
			$this->folderBusinessLayer->purgeDeleted($this->userId, false);
			$folder = $this->folderBusinessLayer->create($folderName, $this->userId);

			$params = array(
				'folders' => array($folder)
			);
			return $params;

		} catch(BusinessLayerConflictException $ex) {
			return $this->error($ex, Http::STATUS_CONFLICT);
		} catch(BusinessLayerValidationException $ex) {
			return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
		}
		
	}


	/**
	 * @NoAdminRequired
	 * 
	 * @param int $folderId
	 */
	public function delete($folderId){
		try {
			$this->folderBusinessLayer->markDeleted($folderId, $this->userId);
		} catch (BusinessLayerException $ex){
			return $this->error($ex, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * @NoAdminRequired
	 */
	public function rename(){
		$folderName = $this->params('folderName');
		$folderId = (int) $this->params('folderId');

		try {
			$folder = $this->folderBusinessLayer->rename($folderId, $folderName, 
				$this->userId);

			$params = array(
				'folders' => array($folder)
			);
			return new JSONResponse($params);
		
		} catch(BusinessLayerConflictException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_CONFLICT);
		
		} catch(BusinessLayerValidationException $ex) {
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_UNPROCESSABLE_ENTITY);
		
		} catch (BusinessLayerException $ex){
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function read(){
		$folderId = (int) $this->params('folderId');
		$highestItemId = (int) $this->params('highestItemId');

		$this->itemBusinessLayer->readFolder($folderId, $highestItemId, $this->userId);

		$params = array(
			'feeds' => $this->feedBusinessLayer->findAll($this->userId)
		);
		return new JSONResponse($params);
	}


	/**
	 * @NoAdminRequired
	 */
	public function restore(){
		$folderId = (int) $this->params('folderId');

		try {
			$this->folderBusinessLayer->unmarkDeleted($folderId, $this->userId);
		} catch (BusinessLayerException $ex){
			return new JSONResponse(array(
				'msg' => $ex->getMessage()
			), Http::STATUS_NOT_FOUND);
		}

	}


}