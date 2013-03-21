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

namespace OCA\News\Db;

use \OCA\AppFramework\Core\API;


class FolderMapper extends NewsMapper {

	public function __construct(API $api) {
		parent::__construct($api, 'news_folders');
	}

	public function find($id, $userId){
		$sql = 'SELECT * FROM `*dbprefix*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';

		$row = $this->findRow($sql, $id, $userId);
		$folder = new Folder();
		$folder->fromRow($row);

		return $folder;
	}


	private function findAllRows($sql, $params=array()){
		$result = $this->execute($sql, $params);
		
		$folders = array();
		while($row = $result->fetchRow()){
			$folder = new Folder();
			$folder->fromRow($row);
			array_push($folders, $folder);
		}

		return $folders;
	}


	public function findAllFromUser($userId){
		$sql = 'SELECT * FROM `*dbprefix*news_folders` ' .
			'WHERE `user_id` = ?';
		$params = array($userId);

		return $this->findAllRows($sql, $params);
	}


	public function findByName($folderName, $userId){
		$sql = 'SELECT * FROM `*dbprefix*news_folders` ' .
			'WHERE `name` = ?' .
			'AND `user_id` = ?';
		$params = array($folderName, $userId);

		return $this->findAllRows($sql, $params);
	}
}