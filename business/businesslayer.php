<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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

namespace OCA\News\Business;

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

// use \OCA\News\Db\Entity;


abstract class BusinessLayer {

	protected $mapper;

	public function __construct($mapper){
		$this->mapper = $mapper;
	}


	public function create($entity){
		$this->validate($entity);
		$this->mapper->create($entity);
	}


	public function update($entity){
		try {
			$this->validate($entity);
			$this->mapper->update($entity);
		} catch(DoesNotExistException $ex){
			$this->throwDoesNotExistException($ex);
		}
	}


	public function delete($id){
		try {
			$this->mapper->delete($id);
		} catch(DoesNotExistException $ex){
			$this->throwDoesNotExistException($ex);
		}
	}


	public function getAll(){
		return $this->mapper->getAllByUserId($this->api->getUserId());
	}


	public function getById($id){
		try {
			$entity = $this->mapper->getByIdAndUserId($id, $this->api->getUserId());
			if($feed->getUserId() !== $this->api->getUserId()){
				throw new PermissionException('Not allowed to change the ' + 
					'feeds of a user other than the current one');
			} else {
				return $entity;
			}
		} catch(DoesNotExistException $ex){
			$this->throwDoesNotExistException($ex);
		} catch(MultipleObjectsReturnedException $ex){
			$this->throwMultipleObjectsReturnedException($ex);
		}
	}


	protected abstract function validate($entity);

	protected abstract function throwDoesNotExistException(DoesNotExistException $ex);
	protected abstract function throwMultipleObjectsReturnedException(MultipleObjectsReturnedException $ex);
	protected abstract function throwObjectExistsException(ObjectExistsException $ex);

}
