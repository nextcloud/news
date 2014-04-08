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

require_once(__DIR__ . "/../../classloader.php");



class MapperFactoryTest extends \PHPUnit_Framework_TestCase {


	public function setUp() {
		$this->api = $this->getMockBuilder('\OCA\AppFramework\Core\API')
			->disableOriginalConstructor()
			->getMock();
	}


	public function testGetItemMapperSqlite() {
		$this->api->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('sqlite'));
		$factory = new MapperFactory($this->api);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperMysql() {
		$this->api->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('mysql'));
		$factory = new MapperFactory($this->api);

		$this->assertTrue($factory->getItemMapper() instanceof ItemMapper);
	}


	public function testGetItemMapperPostgres() {
		$this->api->expects($this->once())
			->method('getSystemValue')
			->with($this->equalTo('dbtype'))
			->will($this->returnValue('pgsql'));
		$factory = new MapperFactory($this->api);

		$this->assertTrue($factory->getItemMapper() instanceof \OCA\News\Db\Postgres\ItemMapper);
	}


}