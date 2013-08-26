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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\IMapper;


abstract class BusinessLayer {

	protected $mapper;

	public function __construct(IMapper $mapper){
		$this->mapper = $mapper;
	}


	/**
	 * Delete an entity
	 * @param int $id the id of the entity
	 * @param string $userId the name of the user for security reasons
	 * @throws DoesNotExistException if the entity does not exist
	 * @throws MultipleObjectsReturnedException if more than one entity exists
	 */
	public function delete($id, $userId){
		$entity = $this->find($id, $userId);
		$this->mapper->delete($entity);
	}


	/**
	 * Finds an entity by id
	 * @param int $id the id of the entity
	 * @param string $userId the name of the user for security reasons
	 * @throws DoesNotExistException if the entity does not exist
	 * @throws MultipleObjectsReturnedException if more than one entity exists
	 * @return Entity the entity
	 */
	public function find($id, $userId){
		try {
			return $this->mapper->find($id, $userId);
		} catch(DoesNotExistException $ex){
			throw new BusinessLayerException($ex->getMessage());
		} catch(MultipleObjectsReturnedException $ex){
			throw new BusinessLayerException($ex->getMessage());
		}
	}

}
