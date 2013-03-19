<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

require_once(__DIR__ . "/../classloader.php");


class TestEntity extends Entity {
	public $name;
	public $email;
};


class EntityTest extends \PHPUnit_Framework_TestCase {

	protected function setUp(){

	}


	public function testFromRow(){
		$row = array(
			'name' => 'john', 
			'email' => 'john@something.com'
		);
		$entity = new TestEntity();

		$entity->fromRow($row);

		$this->assertEquals($row['name'], $entity->name);
		$this->assertEquals($row['email'], $entity->email);
	}


	public function testGetSetId(){
		$id = 3;
		$entity = new TestEntity();
		$entity->setId(3);

		$this->assertEquals($id, $entity->getId());
	}


	public function testColumnToPropertyNoReplacement(){
		$column = 'my';
		$entity = new TestEntity();
		$this->assertEquals('my', 
			$entity->columnToProperty($column));
	}


	public function testColumnToProperty(){
		$column = 'my_attribute';
		$entity = new TestEntity();
		$this->assertEquals('myAttribute', 
			$entity->columnToProperty($column));
	}


	public function testPropertyToColumnNoReplacement(){
		$property = 'my';
		$entity = new TestEntity();
		$this->assertEquals('my', 
			$entity->propertyToColumn($property));
	}


	public function testSetterMarksFieldUpdated(){
		$id = 3;
		$entity = new TestEntity();
		$entity->setId(3);

		$this->assertContains('id', $entity->getUpdatedFields());
	}

}