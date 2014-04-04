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

namespace OCA\News\BusinessLayer;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Utility\TimeFactory;

use \OCA\News\Db\Folder;
use \OCA\News\Db\FolderMapper;


class FolderBusinessLayer extends BusinessLayer {

	private $api;
	private $timeFactory;
	private $autoPurgeMinimumInterval;

	public function __construct(FolderMapper $folderMapper,
	                            API $api,
	                            TimeFactory $timeFactory,
	                            $autoPurgeMinimumInterval){
		parent::__construct($folderMapper);
		$this->api = $api;
		$this->timeFactory = $timeFactory;
		$this->autoPurgeMinimumInterval = $autoPurgeMinimumInterval;
	}

	/**
	 * Returns all folders of a user
	 * @param string $userId the name of the user
	 * @return array of folders
	 */
	public function findAll($userId) {
		return $this->mapper->findAllFromUser($userId);
	}


	private function validateFolder($folderName, $userId){
		$existingFolders = $this->mapper->findByName($folderName, $userId);
		if(count($existingFolders) > 0){

			throw new BusinessLayerConflictException(
				$this->api->getTrans()->t('Can not add folder: Exists already'));
		}

		if(mb_strlen($folderName) === 0) {
			throw new BusinessLayerValidationException('Folder name can not be empty');
		}
	}


	/**
	 * Creates a new folder
	 * @param string $folderName the name of the folder
	 * @param string $userId the name of the user for whom it should be created
	 * @param int $parentId the parent folder id, deprecated we dont nest folders
	 * @throws BusinessLayerConflictException if name exists already
	 * @throws BusinessLayerValidationException if the folder has invalid parameters
	 * @return Folder the newly created folder
	 */
	public function create($folderName, $userId, $parentId=0) {
		$this->validateFolder($folderName, $userId);

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
	 * Renames a folder
	 * @param int $folderId the id of the folder that should be deleted
	 * @param string $folderName the new name of the folder
	 * @param string $userId the name of the user for security reasons
	 * @throws BusinessLayerConflictException if name exists already
	 * @throws BusinessLayerValidationException if the folder has invalid parameters
	 * @throws BusinessLayerException if the folder does not exist
	 * @return Folder the updated folder
	 */
	public function rename($folderId, $folderName, $userId){
		$this->validateFolder($folderName, $userId);

		$folder = $this->find($folderId, $userId);
		$folder->setName($folderName);
		return $this->mapper->update($folder);
	}


	/**
	 * Use this to mark a folder as deleted. That way it can be undeleted
	 * @param int $folderId the id of the folder that should be deleted
	 * @param string $userId the name of the user for security reasons
	 * @throws BusinessLayerException when folder does not exist
	 */
	public function markDeleted($folderId, $userId) {
		$folder = $this->find($folderId, $userId);
		$folder->setDeletedAt($this->timeFactory->getTime());
		$this->mapper->update($folder);
	}


	/**
	 * Use this to restore a folder
	 * @param int $folderId the id of the folder that should be restored
	 * @param string $userId the name of the user for security reasons
	 * @throws BusinessLayerException when folder does not exist
	 */
	public function unmarkDeleted($folderId, $userId) {
		$folder = $this->find($folderId, $userId);
		$folder->setDeletedAt(0);
		$this->mapper->update($folder);
	}


	/**
	 * Deletes all deleted folders
	 * @param string $userId if given it purges only folders of that user
	 * @param boolean $useInterval defaults to true, if true it only purges
	 * entries in a given interval to give the user a chance to undo the
	 * deletion
	 */
	public function purgeDeleted($userId=null, $useInterval=true) {
		$deleteOlderThan = null;

		if ($useInterval) {
			$now = $this->timeFactory->getTime();
			$deleteOlderThan = $now - $this->autoPurgeMinimumInterval;
		}

		$toDelete = $this->mapper->getToDelete($deleteOlderThan, $userId);

		foreach ($toDelete as $folder) {
			$this->mapper->delete($folder);
		}
	}


	/**
	 * Deletes all folders of a user
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$this->mapper->deleteUser($userId);
	}


}
