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

namespace OCA\News\Bl;

use \OCA\News\Db\Folder;
use \OCA\News\Db\FolderMapper;


class FolderBl extends Bl {

	public function __construct(FolderMapper $folderMapper){
		parent::__construct($folderMapper);
	}


	public function findAll($userId) {
		return $this->mapper->findAllFromUser($userId);
	}


	public function create($name, $parentId) {
		$folder = new Folder();
		$folder->setName($name);
		$folder->setParentId($parentId);
		return $this->mapper->insert($folder);
	}


	public function setOpened($folderId, $opened, $userId){
		$folder = $this->find($folderId, $userId);
		$folder->setOpened($opened);
		$this->mapper->update($folder);
	}


	public function rename($folderId, $folderName, $userId){
		$folder = $this->find($folderId, $userId);
		$folder->setName($folderName);
		$this->mapper->update($folder);
	}


}
