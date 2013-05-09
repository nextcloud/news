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

namespace OCA\News\External;

use \OCA\AppFramework\Http\Request;

use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerExistsException;
use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class FeedAPITest extends \PHPUnit_Framework_TestCase {

	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $feedAPI;
	private $api;
	private $user;
	private $request;
	private $msg;

	protected function setUp() {
		$this->api = $this->getMockBuilder(
			'\OCA\AppFramework\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCA\AppFramework\Http\Request')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedAPI = new FeedAPI(
			$this->api,
			$this->request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);
		$this->user = 'tom';
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->msg = 'hohoho';
	}


	public function testGetAll() {
		$feeds = array(
			new Feed()
		);
		$starredCount = 3;
		$newestItemId = 2;

		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($starredCount));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->returnValue($newestItemId));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));

		$response = $this->feedAPI->getAll();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI()),
			'starredCount' => $starredCount,
			'newestItemId' => $newestItemId
		), $response->getData());
	}


	public function testGetAllNoNewestItemId() {
		$feeds = array(
			new Feed()
		);
		$starredCount = 3;

		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($starredCount));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('')));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));

		$response = $this->feedAPI->getAll();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI()),
			'starredCount' => $starredCount,
		), $response->getData());
	}


	public function testDelete() {
		$request = new Request(array('urlParams' => array(
			'feedId' => 2
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->with(
				$this->equalTo(2),
				$this->equalTo($this->user));

		$response = $this->feedAPI->delete();

		$this->assertNull($response->getData());
		$this->assertNull($response->getMessage());
		$this->assertEquals(NewsAPIResult::OK, $response->getStatusCode());
	}


	public function testDeleteDoesNotExist() {
		$request = new Request(array('urlParams' => array(
			'feedId' => 2
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->delete();

		$this->assertNull($response->getData());
		$this->assertEquals($this->msg, $response->getMessage());
		$this->assertEquals(NewsAPIResult::NOT_FOUND_ERROR, $response->getStatusCode());
	}


	public function testCreate() {
		$feeds = array(
			new Feed()
		);
		$request = new Request(array('params' => array(
			'url' => 'ho',
			'folderId' => 3
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with(
				$this->equalTo('ho'),
				$this->equalTo(3),
				$this->equalTo($this->user))
			->will($this->returnValue($feeds[0]));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->returnValue(3));

		$response = $this->feedAPI->create();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI()),
			'newestItemId' => 3
		), $response->getData());

		$this->assertNull($response->getMessage());
		$this->assertEquals(NewsAPIResult::OK, $response->getStatusCode());
	}


	public function testCreateNoItems() {
		$feeds = array(
			new Feed()
		);
		$request = new Request(array('params' => array(
			'url' => 'ho',
			'folderId' => 3
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with(
				$this->equalTo('ho'),
				$this->equalTo(3),
				$this->equalTo($this->user))
			->will($this->returnValue($feeds[0]));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->throwException(new BusinessLayerException('')));

		$response = $this->feedAPI->create();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI())
		), $response->getData());

		$this->assertNull($response->getMessage());
		$this->assertEquals(NewsAPIResult::OK, $response->getStatusCode());
	}



	public function testCreateExists() {
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerExistsException($this->msg)));

		$response = $this->feedAPI->create();

		$this->assertNull($response->getData());
		$this->assertEquals($this->msg, $response->getMessage());
		$this->assertEquals(NewsAPIResult::EXISTS_ERROR, $response->getStatusCode());
	}


	public function testCreateError() {
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->create();

		$this->assertNull($response->getData());
		$this->assertEquals($this->msg, $response->getMessage());
		$this->assertEquals(NewsAPIResult::NOT_FOUND_ERROR, $response->getStatusCode());
	}


	public function testRead() {
		$request = new Request(array(
			'urlParams' => array(
				'feedId' => 3
			),
			'params' => array(
				'newestItemId' => 30,
			)
		));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->itemBusinessLayer->expects($this->once())
			->method('readFeed')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->feedAPI->read();

		$this->assertNull($response->getData());
		$this->assertNull($response->getMessage());
		$this->assertEquals(NewsAPIResult::OK, $response->getStatusCode());
	}


	public function testMove() {
		$request = new Request(array(
			'urlParams' => array(
				'feedId' => 3
			),
			'params' => array(
				'folderId' => 30,
			)
		));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);		

		
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->feedAPI->move();

		$this->assertNull($response->getData());
		$this->assertNull($response->getMessage());
		$this->assertEquals(NewsAPIResult::OK, $response->getStatusCode());
	}


	public function testMoveDoesNotExist() {	
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->move();

		$this->assertNull($response->getData());
		$this->assertEquals($this->msg, $response->getMessage());
		$this->assertEquals(NewsAPIResult::NOT_FOUND_ERROR, $response->getStatusCode());
	}
}