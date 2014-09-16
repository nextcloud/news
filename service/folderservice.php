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

namespace OCA\News\Service;

use \OCP\IL10N;

use \OCA\News\Db\Folder;
use \OCA\News\Db\FolderMapper;
use \OCA\News\Utility\Config;


class FolderService extends Service {

	private $l10n;
	private $timeFactory;
	private $autoPurgeMinimumInterval;
	private $folderMapper;

	public function __construct(FolderMapper $folderMapper,
	                            IL10N $l10n,
	                            $timeFactory,
	                            Config $config){
		parent::__construct($folderMapper);
		$this->l10n = $l10n;
		$this->timeFactory = $timeFactory;
		$this->autoPurgeMinimumInterval = $config->getAutoPurgeMinimumInterval();
		$this->folderMapper = $folderMapper;
	}

	/**
	 * Returns all folders of a user
	 * @param string $userId the name of the user
	 * @return array of folders
	 */
	public function findAll($userId) {
		return $this->folderMapper->findAllFromUser($userId);
	}


	private function validateFolder($folderName, $userId){
		$existingFolders = $this->folderMapper->findByName($folderName, $userId);
		if(count($existingFolders) > 0){

			throw new ServiceConflictException(
				$this->l10n->t('Can not add folder: Exists already'));
		}

		if(mb_strlen($folderName) === 0) {
			throw new ServiceValidationException('Folder name can not be empty');
		}
	}


	/**
	 * Creates a new folder
	 * @param string $folderName the name of the folder
	 * @param string $userId the name of the user for whom it should be created
	 * @param int $parentId the parent folder id, deprecated we don't nest folders
	 * @throws ServiceConflictException if name exists already
	 * @throws ServiceValidationException if the folder has invalid parameters
	 * @return Folder the newly created folder
	 */
	public function create($folderName, $userId, $parentId=0) {
		$this->validateFolder($folderName, $userId);

		$folder = new Folder();
		$folder->setName($folderName);
		$folder->setUserId($userId);
		$folder->setParentId($parentId);
		$folder->setOpened(true);
		return $this->folderMapper->insert($folder);
	}


	/**
	 * @throws ServiceException if the folder does not exist
	 */
	public function open($folderId, $opened, $userId){
		$folder = $this->find($folderId, $userId);
		$folder->setOpened($opened);
		$this->folderMapper->update($folder);
	}


	/**
	 * Renames a folder
	 * @param int $folderId the id of the folder that should be deleted
	 * @param string $folderName the new name of the folder
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceConflictException if name exists already
	 * @throws ServiceValidationException if the folder has invalid parameters
	 * @throws ServiceNotFoundException if the folder does not exist
	 * @return Folder the updated folder
	 */
	public function rename($folderId, $folderName, $userId){
		$this->validateFolder($folderName, $userId);

		$folder = $this->find($folderId, $userId);
		$folder->setName($folderName);
		return $this->folderMapper->update($folder);
	}


	/**
	 * Use this to mark a folder as deleted. That way it can be un-deleted
	 * @param int $folderId the id of the folder that should be deleted
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException when folder does not exist
	 */
	public function markDeleted($folderId, $userId) {
		$folder = $this->find($folderId, $userId);
		$folder->setDeletedAt($this->timeFactory->getTime());
		$this->folderMapper->update($folder);
	}


	/**
	 * Use this to restore a folder
	 * @param int $folderId the id of the folder that should be restored
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException when folder does not exist
	 */
	public function unmarkDeleted($folderId, $userId) {
		$folder = $this->find($folderId, $userId);
		$folder->setDeletedAt(0);
		$this->folderMapper->update($folder);
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

		$toDelete = $this->folderMapper->getToDelete($deleteOlderThan, $userId);

		foreach ($toDelete as $folder) {
			$this->folderMapper->delete($folder);
		}
	}


	/**
	 * Deletes all folders of a user
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$this->folderMapper->deleteUser($userId);
	}


}
