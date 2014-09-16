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

namespace OCA\News\Db;

use \OCP\AppFramework\Db\Entity;

interface IMapper {

    /**
     * @param int $id the id of the feed
     * @param string $userId the id of the user
     * @return \OCP\AppFramework\Db\Entity
     */
	public function find($id, $userId);

	/**
	 * Delete an entity
	 * @param Entity $entity the entity that should be deleted
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if the entity does
     * not exist, or there
	 * are more than one of it
	 */
	public function delete(Entity $entity);
}