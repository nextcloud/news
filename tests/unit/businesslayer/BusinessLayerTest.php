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

namespace OCA\News\BusinessLayer;

require_once(__DIR__ . "/../../classloader.php");


use \OCA\News\Db\DoesNotExistException;
use \OCA\News\Db\MultipleObjectsReturnedException;
use \OCA\News\Db\Folder;


class TestBusinessLayer extends BusinessLayer {
	public function __construct($mapper){
		parent::__construct($mapper);
	}
}

class BusinessLayerTest extends \PHPUnit_Framework_TestCase {

	protected $mapper;
	protected $newsBusinessLayer;

	protected function setUp(){
		$this->mapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->newsBusinessLayer = new TestBusinessLayer($this->mapper);
	}


	public function testDelete(){
		$id = 5;
		$user = 'ken';
		$folder = new Folder();
		$folder->setId($id);

		$this->mapper->expects($this->once())
			->method('delete')
			->with($this->equalTo($folder));
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($user))
			->will($this->returnValue($folder));

		$result = $this->newsBusinessLayer->delete($id, $user);
	}


	public function testFind(){
		$id = 3;
		$user = 'ken';

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($user));

		$result = $this->newsBusinessLayer->find($id, $user);
	}


	public function testFindDoesNotExist(){
		$ex = new DoesNotExistException('hi');

		$this->mapper->expects($this->once())
			->method('find')
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->newsBusinessLayer->find(1, '');
	}


	public function testFindMultiple(){
		$ex = new MultipleObjectsReturnedException('hi');

		$this->mapper->expects($this->once())
			->method('find')
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->newsBusinessLayer->find(1, '');
	}

}
