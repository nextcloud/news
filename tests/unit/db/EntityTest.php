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


class TestEntity extends Entity {
	public $name;
	public $email;
	public $testId;
	public $preName;

	public function __construct(){
		$this->addType('testId', 'integer');		
	}
};


class EntityTest extends \PHPUnit_Framework_TestCase {

	private $entity;

	protected function setUp(){
		$this->entity = new TestEntity();
	}


	public function testResetUpdatedFields(){
		$entity = new TestEntity();
		$entity->setId(3);
		$entity->resetUpdatedFields();

		$this->assertEquals(array(), $entity->getUpdatedFields());
	}


	public function testFromRow(){
		$row = array(
			'pre_name' => 'john', 
			'email' => 'john@something.com'
		);
		$this->entity = TestEntity::fromRow($row);

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

		$this->entity = TestEntity::fromRow($row);
		$this->assertEquals($entity2, $this->entity);
	}


	public function testIdGetsConvertedToInt(){
		$row = array('id' => '4');

		$this->entity = TestEntity::fromRow($row);
		$this->assertSame(4, $this->entity->getId());
	}


	public function testSetType(){
		$row = array('testId' => '4');

		$this->entity = TestEntity::fromRow($row);
		$this->assertSame(4, $this->entity->getTestId());
	}


	public function testFromParams(){
		$params = array(
			'testId' => 4,
			'email' => 'john@doe'
		);

		$entity = TestEntity::fromParams($params);

		$this->assertEquals($params['testId'], $entity->getTestId());
		$this->assertEquals($params['email'], $entity->getEmail());
		$this->assertTrue($entity instanceof TestEntity);
	}

	public function testSlugify(){
		$entity = new TestEntity();
		$entity->setName('Slugify this!');
		$this->assertEquals('slugify-this', $entity->slugify('name'));
		$entity->setName('°!"§$%&/()=?`´ß\}][{³²#\'+~*-_.:,;<>|äöüÄÖÜSlugify this!');
		$this->assertEquals('slugify-this', $entity->slugify('name'));
	}


	public function testSetterCasts() {
		$entity = new TestEntity();
		$entity->setId('3');
		$this->assertSame(3, $entity->getId());
	}


	public function testSetterDoesNotCastOnNull() {
		$entity = new TestEntity();
		$entity->setId(null);
		$this->assertSame(null, $entity->getId());
	}


	public function testGetFieldTypes() {
		$entity = new TestEntity();
		$this->assertEquals(array(
			'id' => 'integer',
			'testId' => 'integer'
		), $entity->getFieldTypes());
	}


	public function testGetItInt() {
		$entity = new TestEntity();
		$entity->setId(3);
		$this->assertEquals('integer', gettype($entity->getId()));
	}

}