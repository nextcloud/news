<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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

namespace OCA\News\API;

use \OCP\IRequest;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Utility\ControllerTestUtility;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class FeedAPITest extends ControllerTestUtility {

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
			'\OCA\News\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
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
		$this->msg = 'hohoho';
	}


	private function assertDefaultAnnotations($methodName){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, $methodName, $annotations);
	}


	public function testGetAllAnnotations(){
		$this->assertDefaultAnnotations('getAll');
	}


	public function testCreateAnnotations(){
		$this->assertDefaultAnnotations('create');
	}


	public function testDeleteAnnotations(){
		$this->assertDefaultAnnotations('delete');
	}


	public function testMoveAnnotations(){
		$this->assertDefaultAnnotations('move');
	}


	public function testReadAnnotations(){
		$this->assertDefaultAnnotations('read');
	}


	public function testGetAllFromUsersAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, 'getAllFromAllUsers', $annotations);
	}


	public function testUpdateAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, 'update', $annotations);
	}


	public function testGetAll() {
		$feeds = array(
			new Feed()
		);
		$starredCount = 3;
		$newestItemId = 2;

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->with(
				$this->equalTo(2),
				$this->equalTo($this->user));

		$response = $this->feedAPI->delete();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testDeleteDoesNotExist() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->delete();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testCreate() {
		$feeds = array(
			new Feed()
		);
		$request = $this->getRequest(array('params' => array(
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
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

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testCreateNoItems() {
		$feeds = array(
			new Feed()
		);
		$request = $this->getRequest(array('params' => array(
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
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

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}



	public function testCreateExists() {
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerConflictException($this->msg)));

		$response = $this->feedAPI->create();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
	}


	public function testCreateError() {
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->create();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testRead() {
		$request = $this->getRequest(array(
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('readFeed')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->feedAPI->read();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testMove() {
		$request = $this->getRequest(array(
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->feedAPI->move();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testRename() {
		$feedId = 3;
		$feedTitle = 'test';

		$request = $this->getRequest(array(
			'urlParams' => array(
				'feedId' => $feedId
			),
			'params' => array(
				'feedTitle' => $feedTitle
			)
		));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('rename')
			->with(
				$this->equalTo($feedId),
				$this->equalTo($feedTitle),
				$this->equalTo($this->user));

		$response = $this->feedAPI->rename();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testMoveDoesNotExist() {
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->move();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testGetAllFromAllUsers(){
		$feed = new Feed();
		$feed->setUrl(3);
		$feed->setId(1);
		$feed->setUserId('john');
		$feeds = array($feed);
		$this->feedBusinessLayer->expects($this->once())
			->method('findAllFromAllUsers')
			->will($this->returnValue($feeds));
		$response = $this->feedAPI->getAllFromAllUsers();
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals('{"feeds":[{"id":1,"userId":"john"}]}', $response->render());
	}


	public function testUpdate() {
		$feedId = 3;
		$userId = 'hi';
		$request = $this->getRequest(array('params' => array(
			'feedId' => $feedId,
			'userId' => $userId
		)));
		$this->feedAPI = new FeedAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer
		);
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo($feedId), $this->equalTo($userId));

		$response = $this->feedAPI->update();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUpdateError() {
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->will($this->throwException(new \Exception($this->msg)));
		$this->api->expects($this->once())
			->method('log')
			->with($this->equalTo('Could not update feed ' . $this->msg),
				$this->equalTo('debug'));

		$response = $this->feedAPI->update();

		$this->assertTrue($response instanceof JSONResponse);

	}


}
