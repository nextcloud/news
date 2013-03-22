<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
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

namespace OCA\News\Bl;

require_once(__DIR__ . "/../classloader.php");


use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;
use \OCA\News\Db\Folder;


class TestBl extends BL {
	public function __construct($mapper){
		parent::__construct($mapper);
	}
}

class BlTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $mapper;
	protected $newsBl;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->mapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->newsBl = new TestBl($this->mapper);
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

		$result = $this->newsBl->delete($id, $user);
	}


	public function testFind(){
		$id = 3;
		$user = 'ken';

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($user));

		$result = $this->newsBl->find($id, $user);
	}


	public function testFindDoesNotExist(){
		$ex = new DoesNotExistException('hi');

		$this->mapper->expects($this->once())
			->method('find')
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\Bl\BLException');
		$this->newsBl->find(1, '');
	}


	public function testFindMultiple(){
		$ex = new MultipleObjectsReturnedException('hi');

		$this->mapper->expects($this->once())
			->method('find')
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\Bl\BLException');
		$this->newsBl->find(1, '');
	}

}
