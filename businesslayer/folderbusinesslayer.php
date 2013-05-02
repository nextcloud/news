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

namespace OCA\News\BusinessLayer;

use \OCA\AppFramework\Core\API;

use \OCA\News\Db\Folder;
use \OCA\News\Db\FolderMapper;


class FolderBusinessLayer extends BusinessLayer {

	private $api;

	public function __construct(FolderMapper $folderMapper,
	                            API $api){
		parent::__construct($folderMapper);
		$this->api = $api;
	}


	public function findAll($userId) {
		return $this->mapper->findAllFromUser($userId);
	}


	private function allowNoNameTwice($folderName, $userId){
		$existingFolders = $this->mapper->findByName($folderName, $userId);
		if(count($existingFolders) > 0){

			throw new BusinessLayerExistsException(
				$this->api->getTrans()->t('Can not add folder: Exists already'));
		}
	}

	/**
	 * @throws BusinessLayerExistsException if name exists already
	 */
	public function create($folderName, $userId, $parentId=0) {
		$this->allowNoNameTwice($folderName, $userId);

		$folder = new Folder();
		$folder->setName($folderName);
		$folder->setUserId($userId);
		$folder->setParentId($parentId);
		$folder->setOpened(true);
		return $this->mapper->insert($folder);
	}

	/**
	 * @throws BusinessLayerException if the folder does not exist
	 */
	public function open($folderId, $opened, $userId){
		$folder = $this->find($folderId, $userId);
		$folder->setOpened($opened);
		$this->mapper->update($folder);
	}


	/**
	 * @throws BusinessLayerExistsException if name exists already
	 * @throws BusinessLayerException if the folder does not exist
	 */
	public function rename($folderId, $folderName, $userId){
		$this->allowNoNameTwice($folderName, $userId);

		$folder = $this->find($folderId, $userId);
		$folder->setName($folderName);
		$this->mapper->update($folder);
	}



}
