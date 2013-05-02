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

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerExistsException;


class FolderAPI extends Controller {

	private $folderBusinessLayer;

	public function __construct(API $api, 
	                            Request $request,
	                            FolderBusinessLayer $folderBusinessLayer){
		parent::__construct($api, $request);
		$this->folderBusinessLayer = $folderBusinessLayer;
	}


	public function getAll() {
		$userId = $this->api->getUserId();
		$result = array(
			'folders' => array()
		);

		foreach ($this->folderBusinessLayer->findAll($userId) as $folder) {
			array_push($result['folders'], $folder->toAPI());
		}

		return new NewsAPIResult($result);
	}


	public function create() {		
		$userId = $this->api->getUserId();
		$folderName = $this->params('name');
		$result = array(
			'folders' => array()
		);

		try {
			$folder = $this->folderBusinessLayer->create($folderName, $userId);
			array_push($result['folders'], $folder->toAPI());

			return new NewsAPIResult($result);
		} catch(BusinessLayerExistsException $ex) {
			return new NewsAPIResult(null, NewsAPIResult::EXISTS_ERROR, 
				$ex->getMessage());
		}
	}


	public function delete() {
		$userId = $this->api->getUserId();
		$folderId = $this->params('folderId');

		try {
			$this->folderBusinessLayer->delete($folderId, $userId);
			return new NewsAPIResult();
		} catch(BusinessLayerException $ex) {
			return new NewsAPIResult(null, NewsAPIResult::NOT_FOUND_ERROR, 
				$ex->getMessage());
		}
	}


	public function update() {
		$userId = $this->api->getUserId();
		$folderId = $this->params('folderId');
		$folderName = $this->params('name');

		try {
			$this->folderBusinessLayer->rename($folderId, $folderName, $userId);
			return new NewsAPIResult();

		} catch(BusinessLayerExistsException $ex) {
			return new NewsAPIResult(null, NewsAPIResult::EXISTS_ERROR, 
				$ex->getMessage());

		} catch(BusinessLayerException $ex) {
			return new NewsAPIResult(null, NewsAPIResult::NOT_FOUND_ERROR, 
				$ex->getMessage());
		}
	}


}
