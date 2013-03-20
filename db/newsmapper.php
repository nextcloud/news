<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;


abstract class NewsMapper extends Mapper {
	

	public function __construct(API $api, $tableName) {
		parent::__construct($api, $tableName);
	}


	protected function findRow($sql, $id, $userId){
		
		$result = $this->execute($sql, array($id, $userId));
		
		$row = $result->fetchRow();

		if($row === false){
			throw new DoesNotExistException('Item does not exist!');
		} elseif($result->fetchRow() !== false) {
			throw new MultipleObjectsReturnedException('More than one result for Item with id ' . $id . '!');
		} else {
			return $row;
		}
	}


}