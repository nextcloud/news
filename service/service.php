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

use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\NewsMapper;


abstract class Service {

	protected $mapper;

	public function __construct(NewsMapper $mapper){
		$this->mapper = $mapper;
	}


	/**
	 * Delete an entity
	 * @param int $id the id of the entity
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException if the entity does not exist, or there
	 * are more than one of it
	 */
	public function delete($id, $userId){
		$entity = $this->find($id, $userId);
		$this->mapper->delete($entity);
	}


	/**
	 * Finds an entity by id
	 * @param int $id the id of the entity
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException if the entity does not exist, or there
	 * are more than one of it
	 * @return \OCP\AppFramework\Db\Entity the entity
	 */
	public function find($id, $userId){
		try {
			return $this->mapper->find($id, $userId);
		} catch(DoesNotExistException $ex){
			throw new ServiceNotFoundException($ex->getMessage());
		} catch(MultipleObjectsReturnedException $ex){
			throw new ServiceNotFoundException($ex->getMessage());
		}
	}

}
