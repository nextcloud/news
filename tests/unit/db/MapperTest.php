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

use OCA\News\Core\Db;
use OCA\News\Utility\MapperTestUtility;


require_once(__DIR__ . "/../../classloader.php");


class Example extends Entity {
	public $preName;
	public $email;
};


class ExampleMapper extends Mapper {
	public function __construct(Db $db){ parent::__construct($db, 'table'); }
	public function find($table, $id){ return $this->findOneQuery($table, $id); }
	public function findOneEntity($table, $id){ return $this->findEntity($table, $id); }
	public function findAll($table){ return $this->findAllQuery($table); }
	public function findAllEntities($table){ return $this->findEntities($table); }
	public function mapRow($row){ return $this->mapRowToEntity($row); }
	public function pDeleteQuery($table, $id){ $this->deleteQuery($table, $id); }
}


class MapperTest extends MapperTestUtility {

	private $mapper;

	public function setUp(){
		$this->beforeEach();
		$this->db = $this->getMockBuilder(
			'\OCA\News\Core\Db')
			->disableOriginalConstructor()
			->getMock();
		$this->mapper = new ExampleMapper($this->db);
	}


	public function testMapperShouldSetTableName(){
		$this->assertEquals('*PREFIX*table', $this->mapper->getTableName());
	}


	public function testFindQuery(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array(
			array('hi')
		);
		$this->setMapperResult($sql, $params, $rows);		
		$this->mapper->find($sql, $params);
	}

	public function testFindEntity(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array(
			array('pre_name' => 'hi')
		);
		$this->setMapperResult($sql, $params, $rows);
		$this->mapper->findOneEntity($sql, $params);
	}

	public function testFindNotFound(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array();
		$this->setMapperResult($sql, $params, $rows);		
		$this->setExpectedException(
			'\OCA\News\Db\DoesNotExistException');
		$this->mapper->find($sql, $params);
	}

	public function testFindEntityNotFound(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array();
		$this->setMapperResult($sql, $params, $rows);
		$this->setExpectedException(
			'\OCA\News\Db\DoesNotExistException');
		$this->mapper->findOneEntity($sql, $params);
	}

	public function testFindMultiple(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array(
			array('jo'), array('ho')
		);
		$this->setMapperResult($sql, $params, $rows);
		$this->setExpectedException(
			'\OCA\News\Db\MultipleObjectsReturnedException');
		$this->mapper->find($sql, $params);
	}

	public function testFindEntityMultiple(){
		$sql = 'hi';
		$params = array('jo');
		$rows = array(
			array('jo'), array('ho')
		);
		$this->setMapperResult($sql, $params, $rows);
		$this->setExpectedException(
			'\OCA\News\Db\MultipleObjectsReturnedException');
		$this->mapper->findOneEntity($sql, $params);
	}


	public function testDelete(){
		$sql = 'DELETE FROM `*PREFIX*table` WHERE `id` = ?';
		$params = array(2);

		$this->setMapperResult($sql, $params);
		$entity = new Example();
		$entity->setId($params[0]);

		$this->mapper->delete($entity);
	}


	public function testCreate(){
		$this->db->expects($this->once())
			->method('getInsertId')
			->with($this->equalTo('*PREFIX*table'))
			->will($this->returnValue(3));
		$this->mapper = new ExampleMapper($this->db);

		$sql = 'INSERT INTO `*PREFIX*table`(`pre_name`,`email`) ' .
				'VALUES(?,?)';
		$params = array('john', 'my@email');
		$entity = new Example();
		$entity->setPreName($params[0]);
		$entity->setEmail($params[1]);

		$this->setMapperResult($sql, $params);

		$this->mapper->insert($entity);
	}


	public function testCreateShouldReturnItemWithCorrectInsertId(){
		$this->db->expects($this->once())
			->method('getInsertId')
			->with($this->equalTo('*PREFIX*table'))
			->will($this->returnValue(3));
		$this->mapper = new ExampleMapper($this->db);

		$sql = 'INSERT INTO `*PREFIX*table`(`pre_name`,`email`) ' .
				'VALUES(?,?)';
		$params = array('john', 'my@email');
		$entity = new Example();
		$entity->setPreName($params[0]);
		$entity->setEmail($params[1]);

		$this->setMapperResult($sql, $params);

		$result = $this->mapper->insert($entity);

		$this->assertEquals(3, $result->getId());
	}


	public function testUpdate(){
		$sql = 'UPDATE `*PREFIX*table` ' .
				'SET ' .
				'`pre_name` = ?,'.
				'`email` = ? ' .
				'WHERE `id` = ?';

		$params = array('john', 'my@email', 1);
		$entity = new Example();
		$entity->setPreName($params[0]);
		$entity->setEmail($params[1]);
		$entity->setId($params[2]);

		$this->setMapperResult($sql, $params);

		$this->mapper->update($entity);
	}


	public function testUpdateNoId(){
		$params = array('john', 'my@email');
		$entity = new Example();
		$entity->setPreName($params[0]);
		$entity->setEmail($params[1]);

		$this->setExpectedException('InvalidArgumentException');

		$this->mapper->update($entity);
	}


	public function testMapRowToEntity(){
		$entity1 = $this->mapper->mapRow(array('pre_name' => 'test1', 'email' => 'test2'));
		$entity2 = new Example();
		$entity2->setPreName('test1');
		$entity2->setEmail('test2');
		$entity2->resetUpdatedFields();
		$this->assertEquals($entity2, $entity1);
	}

	public function testFindEntities(){
		$sql = 'hi';
		$rows = array(
			array('pre_name' => 'hi')
		);
		$entity = new Example();
		$entity->setPreName('hi');
		$entity->resetUpdatedFields();
		$this->setMapperResult($sql, array(), $rows);
		$result = $this->mapper->findAllEntities($sql);
		$this->assertEquals(array($entity), $result);
	}

	public function testFindEntitiesNotFound(){
		$sql = 'hi';
		$rows = array();
		$this->setMapperResult($sql, array(), $rows);
		$result = $this->mapper->findAllEntities($sql);
		$this->assertEquals(array(), $result);
	}

	public function testFindEntitiesMultiple(){
		$sql = 'hi';
		$rows = array(
			array('pre_name' => 'jo'), array('email' => 'ho')
		);
		$entity1 = new Example();
		$entity1->setPreName('jo');
		$entity1->resetUpdatedFields();
		$entity2 = new Example();
		$entity2->setEmail('ho');
		$entity2->resetUpdatedFields();
		$this->setMapperResult($sql, array(), $rows);
		$result = $this->mapper->findAllEntities($sql);
		$this->assertEquals(array($entity1, $entity2), $result);
	}
}