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


use \OCA\News\Db\Item;


class ItemBlTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $mapper;
	protected $bl;
	protected $user;
	protected $response;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->mapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->bl = new ItemBl($this->mapper);
		$this->user = 'jack';
		$response = 'hi';
	}




	/*
	public function testFindAll(){
		$this->mapper->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->bl->findAllFromUser($this->user);
		$this->assertEquals($this->response, $result);
	}

	*/

	public function testStarredCount(){
		$star = 18;

		$this->mapper->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($star));

		$result = $this->bl->starredCount($this->user);

		$this->assertEquals($star, $result);
	}


	public function testStar(){
		$itemId = 3;
		$item = new Item();
		$item->setStatus(128);
		$item->setId($itemId);

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($itemId), $this->equalTo($this->user))
			->will($this->returnValue($item));

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($item));

		$this->bl->star($itemId, false, $this->user);

		$this->assertTrue($item->isUnstarred());
	}


	public function testRead(){
		$itemId = 3;
		$item = new Item();
		$item->setStatus(128);
		$item->setId($itemId);

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($itemId), $this->equalTo($this->user))
			->will($this->returnValue($item));

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($item));

		$this->bl->read($itemId, false, $this->user);

		$this->assertTrue($item->isUnread());
	}


	public function testReadFeed(){
		$feedId = 3;
		
		$this->mapper->expects($this->once())
			->method('readFeed')
			->with($this->equalTo($feedId), $this->equalTo($this->user));

		$this->bl->readFeed($feedId, $this->user);
	}


	public function testCreate(){
		$item = new Item();

		$this->mapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($item));

		$this->bl->create($item, $this->user);	
	}

}






