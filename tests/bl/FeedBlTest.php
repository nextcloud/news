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


use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use \OCA\News\Utility\FeedFetcher;
use \OCA\News\Utility\FetcherException;

class FeedBlTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $mapper;
	protected $bl;
	protected $user;
	protected $response;
	protected $fetcher;
	protected $itemBl;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->mapper = $this->getMockBuilder('\OCA\News\Db\FeedMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->fetcher = $this->getMockBuilder('\OCA\News\Utility\FeedFetcher')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBl = $this->getMockBuilder('\OCA\News\Bl\ItemBl')
			->disableOriginalConstructor()
			->getMock();
		$this->bl = new FeedBl($this->mapper, $this->fetcher, $this->itemBl);
		$this->user = 'jack';
		$response = 'hi';
	}


	public function testFindAll(){
		$this->mapper->expects($this->once())
			->method('findAll')
			->will($this->returnValue($this->response));

		$result = $this->bl->findAll();
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllFromUser(){
		$this->mapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->bl->findAllFromUser($this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testCreateDoesNotFindFeed(){
		$ex = new FetcherException('hi');
		$url = 'test';
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->throwException($ex));
		$this->setExpectedException('\OCA\News\Bl\BLException');
		$this->bl->create($url, 1, 2);
	}

	public function testCreate(){
		$url = 'test';
		$folderId = 10;
		$createdFeed = new Feed();
		$createdFeed->setUrl($url);
		$return = array(
			$createdFeed,
			array(new Item(), new Item())
		);
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->returnValue($return));
		$this->mapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($createdFeed))
			->will($this->returnValue($createdFeed));
		$this->itemBl->expects($this->at(0))
			->method('create')
			->with($this->equalTo($return[1][0]));
		$this->itemBl->expects($this->at(1))
			->method('create')
			->with($this->equalTo($return[1][1]));
		
		$feed = $this->bl->create($url, $folderId, $this->user);

		$this->assertEquals($feed->getFolderId(), $folderId);
		$this->assertEquals($feed->getUrl(), $url);
	}


	public function testUpdate(){
		// TODO
	}


	public function testMove(){
		$feedId = 3;
		$folderId = 4;
		$feed = new Feed();
		$feed->setFolderId(16);
		$feed->setId($feedId);

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($feedId), $this->equalTo($this->user))
			->will($this->returnValue($feed));

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($feed));

		$this->bl->move($feedId, $folderId, $this->user);

		$this->assertEquals($folderId, $feed->getFolderId());
	}


}