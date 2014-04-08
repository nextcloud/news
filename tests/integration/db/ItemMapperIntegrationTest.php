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

use \OCA\News\Utility\MapperTestUtility;

require_once(__DIR__ . "/../../classloader.php");


class InMemoryDatabase {

	private $db;

	public function __construct(){
		$this->db = new \PDO('sqlite::memory:');
	}


	public function prepare($sql){
		$count = 1;
		$sql = str_replace('*PREFIX*', 'oc', $sql, $count);
		var_dump($this->db->prepare($sql));
		return $this->db->prepare($sql);
	}


}



class ItemMapperIntegrationTest extends MapperTestUtility {

	protected $api;

	private $mapper;
	private $db;

	protected function setUp(){
		$db = new InMemoryDatabase();
		$prepare = function($sql) use ($db){
			return $db->prepare($sql);
		};

		$this->api = $this->getMock('OCA\News\Core\API', 
			array('prepareQuery', 'getInsertId'), array('news'));
		$this->api->expects($this->any())
			->method('prepareQuery')
			->will($this->returnCallback($prepare));
		$this->mapper = new ItemMapper($this->api);
	}


	public function testFind(){
		//$this->mapper->find(3, 'john');
	}


}