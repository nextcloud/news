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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\NewsMapper;


abstract class Bl {

	protected $mapper;

	public function __construct(NewsMapper $mapper){
		$this->mapper = $mapper;
	}


	public function delete($id, $userId){
		$entity = $this->find($id, $userId);
		$this->mapper->delete($entity);
	}


	public function find($id, $userId){
		try {
			return $this->mapper->find($id, $userId);
		} catch(DoesNotExistException $ex){
			throw new BLException($ex->getMessage());
		} catch(MultipleObjectsReturnedException $ex){
			throw new BLException($ex->getMessage());
		}
	}

}