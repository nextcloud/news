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

use \OCP\IDb;
use \OCP\AppFramework\Db\Entity;
use \OCP\AppFramework\Db\Mapper;

abstract class NewsMapper extends Mapper {

    public function __construct(IDb $db, $table, $entity) {
        parent::__construct($db, $table, $entity);
    }

    /**
     * @param int $id the id of the feed
     * @param string $userId the id of the user
     * @return \OCP\AppFramework\Db\Entity
     */
	public abstract function find($id, $userId);


}