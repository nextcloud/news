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

	public function __construct(){
		parent::__construct('entity');
	}
};


class EntityTest extends \PHPUnit_Framework_TestCase {

	private $entity;

	protected function setUp(){
		$this->entity = new TestEntity();
	}


	public function testFromRow(){
		$row = array(
			'pre_name' => 'john', 
			'email' => 'john@something.com'
		);
		$this->entity = new TestEntity();

		$this->entity->fromRow($row);

		$this->assertEquals($row['pre_name'], $this->entity->getPreName());
		$this->assertEquals($row['email'], $this->entity->getEmail());
	}


	public function testGetSetId(){
		$id = 3;
		$this->entity->setId(3);

		$this->assertEquals($id, $this->entity->getId());
	}


	public function testColumnToPropertyNoReplacement(){
		$column = 'my';
		$this->assertEquals('my', 
			$this->entity->columnToProperty($column));
	}


	public function testColumnToProperty(){
		$column = 'my_attribute';
		$this->assertEquals('myAttribute', 
			$this->entity->columnToProperty($column));
	}


	public function testPropertyToColumnNoReplacement(){
		$property = 'my';
		$this->assertEquals('my', 
			$this->entity->propertyToColumn($property));
	}


	public function testSetterMarksFieldUpdated(){
		$id = 3;
		$this->entity->setId(3);

		$this->assertContains('id', $this->entity->getUpdatedFields());
	}


	public function testCallShouldOnlyWorkForGetterSetter(){
		$this->setExpectedException('\BadFunctionCallException');

		$this->entity->something();
	}


	public function testGetterShouldFailIfAttributeNotDefined(){
		$this->setExpectedException('\BadFunctionCallException');

		$this->entity->getTest();
	}


	public function testSetterShouldFailIfAttributeNotDefined(){
		$this->setExpectedException('\BadFunctionCallException');

		$this->entity->setTest();
	}


	public function testFromRowShouldNotAssignEmptyArray(){
		$row = array();
		$entity2 = new TestEntity();

		$this->entity->fromRow($row);
		$this->assertEquals($entity2, $this->entity);
	}


	public function testEntityShouldSetTableName(){
		$this->assertEquals('*dbprefix*entity', $this->entity->getTableName());
	}

}