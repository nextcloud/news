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


class Folder extends Entity implements IAPI {

	public $parentId;
	public $name;
	public $userId;
	public $opened;
	public $deletedAt;

	public function __construct(){
		$this->addType('parentId', 'integer');
		$this->addType('opened', 'boolean');
		$this->addType('deletedAt', 'integer');
	}


	public function toAPI() {
		return array(
			'id' => $this->getId(),
			'name' => $this->getName()
		);
	}
}