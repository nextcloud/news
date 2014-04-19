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

namespace OCA\News\Db;

use \OCA\News\Core\Settings;
use \OCA\News\Core\Db;


class MapperFactory {

	private $settings;

	public function __construct(Settings $settings, Db $db) {
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