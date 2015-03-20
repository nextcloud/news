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

use \OCP\IDBConnection;
use \OCA\News\Db\Mysql\ItemMapper as MysqlItemMapper;

class MapperFactory {

	private $dbType;
	private $db;

	public function __construct($DatabaseType, IDBConnection $db) {
		$this->dbType = $DatabaseType;
		$this->db = $db;
	}


	public function getItemMapper() {
		switch($this->dbType) {
			case 'mysql':
				return new MysqlItemMapper($this->db);
			default:
				return new ItemMapper($this->db);
		}
	}


}
