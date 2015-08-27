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

use PHPUnit_Framework_TestCase;

use OCP\IDb;

use OCA\News\Db\Mysql\ItemMapper as MysqlMapper;


class MapperFactoryTest extends PHPUnit_Framework_TestCase {

	private $db;
	private $settings;

	public function setUp() {
		$this->db = $this->getMockBuilder(IDb::class)
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGetItemMapperSqlite() {
		$factory = new MapperFactory($this->db, 'sqlite');
		$this->assertTrue($factory->build() instanceof ItemMapper);
	}

	public function testGetItemMapperPostgres() {
		$factory = new MapperFactory($this->db, 'pgsql');
		$this->assertTrue($factory->build() instanceof ItemMapper);
	}

	public function testGetItemMapperMysql() {
		$factory = new MapperFactory($this->db, 'mysql');
		$this->assertTrue($factory->build() instanceof MysqlMapper);
	}

}
