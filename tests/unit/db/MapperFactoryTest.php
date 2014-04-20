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
		$this->settings = $this->getMockBuilder('\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->db = $this->getMockBuilder('\OCA\News\Core\Db')
			->disableOriginalConstructor()
			->getMock();
	}


	public function testGetItemMapperSqlite() {
		$this->settings->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('sqlite'));
		$factory = new MapperFactory($this->settings, $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperMysql() {
		$this->settings->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('mysql'));
		$factory = new MapperFactory($this->settings, $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperPostgres() {
		$this->settings->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('pgsql'));
		$factory = new MapperFactory($this->settings, $this->db);

		$this->assertTrue($factory->getItemMapper() instanceof \OCA\News\Db\Postgres\ItemMapper);
	}


}