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

namespace OCA\News\BusinessLayer;

use \OCA\News\Db\Folder;
use \OCA\News\Db\FolderMapper;
use \OCA\News\Utility\Config;


class FolderBusinessLayer extends BusinessLayer {

	private $l10n;
	private $timeFactory;
	private $autoPurgeMinimumInterval;

	public function __construct(FolderMapper $folderMapper,
	                            $l10n,
	                            $timeFactory,
	                            Config $config){
		parent::__construct($folderMapper);
		$this->l10n = $l10n;
		$this->timeFactory = $timeFactory;
		$this->autoPurgeMinimumInterval = $config->getAutoPurgeMinimumInterval();
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
				$this->l10n->t('Can not add folder: Exists already'));
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
