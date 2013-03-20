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


use \OCA\News\Db\Folder;


class FolderBlTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $folderMapper;
	protected $folderBl;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->folderMapper = $this->getMock(
			'\OCA\News\Db\NewsMapper',
			array('findAllFromUser', 'insert', 'update', 'find'),
			array($this->api, 'test'));
		$this->folderBl = new FolderBl($this->folderMapper);
	}


	function testGetAll(){
		$userId = 'jack';
		$return = 'hi';
		$this->folderMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($userId))
			->will($this->returnValue($return));

		$result = $this->folderBl->getAll($userId);

		$this->assertEquals($return, $result);
	}


	public function testCreate(){
		$folder = new Folder();
		$folder->setName('hey');
		$folder->setParentId(5);

		$this->folderMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($folder))
			->will($this->returnValue($folder));

		$result = $this->folderBl->create('hey', 5);

		$this->assertEquals($folder, $result);
	}


	public function testSetOpened(){
		$folder = new Folder();

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($folder));

		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder));

		$this->folderBl->setOpened(3, false, '');

		$this->assertFalse($folder->getOpened());

	}

}
