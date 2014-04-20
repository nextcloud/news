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

use \OCP\IConfig;

use \OCA\News\Core\Db;


class MapperFactory {

	private $settings;

	public function __construct(IConfig $settings, Db $db) {
		$this->settings = $settings;
		$this->db = $db;
	}


	public function getItemMapper() {
		switch($this->settings->getSystemValue('dbtype')) {
			case 'pgsql':
				return new \OCA\News\Db\Postgres\ItemMapper($this->db);
				break;
			default:
				return new ItemMapper($this->db);
				break;
		}
	}


}