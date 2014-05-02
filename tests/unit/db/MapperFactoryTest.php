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

require_once(__DIR__ . "/../../classloader.php");



class MapperFactoryTest extends \PHPUnit_Framework_TestCase {

	private $db;
	private $settings;

	public function setUp() {
		$this->db = $this->getMockBuilder('\OCA\News\Core\Db')
			->disableOriginalConstructor()
			->getMock();
	}


	public function testGetItemMapperSqlite() {
		$factory = new MapperFactory('sqlite', $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperMysql() {
		$factory = new MapperFactory('mysql', $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperPostgres() {
		$factory = new MapperFactory('pgsql', $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof \OCA\News\Db\Postgres\ItemMapper);
	}


}