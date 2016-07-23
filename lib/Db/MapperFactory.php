<?php
/**
 * Nextcloud - News
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

use OCA\News\Utility\Time;
use OCP\IDBConnection;

use OCA\News\Db\Mysql\ItemMapper as MysqlItemMapper;
use OCA\News\DependencyInjection\IFactory;


class MapperFactory implements IFactory {

	private $dbType;
	private $db;
    /**
     * @var Time
     */
    private $time;

    public function __construct(IDBConnection $db, $databaseType, Time $time) {
		$this->dbType = $databaseType;
		$this->db = $db;
        $this->time = $time;
    }

	public function build() {
		switch($this->dbType) {
			case 'mysql':
				return new MysqlItemMapper($this->db, $this->time);
			default:
				return new ItemMapper($this->db, $this->time);
		}
	}

}
