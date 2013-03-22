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


class FeedBlTest extends \OCA\AppFramework\Utility\TestUtility {

	protected $api;
	protected $feedMapper;
	protected $feedBl;
	protected $user;
	protected $response;
	protected $utils;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->feedMapper = $this->getMockBuilder('\OCA\News\Db\FeedMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->utils = $this->getMockBuilder('\OCA\News\Utility\FeedFetcher')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBl = new FeedBl($this->feedMapper, $this->utils);
		$this->user = 'jack';
		$response = 'hi';
	}


	public function testFindAll(){
		$this->feedMapper->expects($this->once())
			->method('findAll')
			->will($this->returnValue($this->response));

		$result = $this->feedBl->findAll();
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllFromUser(){
		$this->feedMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->feedBl->findAllFromUser($this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testCreate(){
		// TODO
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

		$this->feedMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($feedId), $this->equalTo($this->user))
			->will($this->returnValue($feed));

		$this->feedMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($feed));

		$this->feedBl->move($feedId, $folderId, $this->user);

		$this->assertEquals($folderId, $feed->getFolderId());
	}


}