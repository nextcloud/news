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

namespace OCA\News\Controller;

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


class FeedApiControllerTest extends ControllerTestUtility {

	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $feedAPI;
	private $appName;
	private $user;
	private $request;
	private $msg;
	private $logger;

	protected function setUp() {
		$this->user = 'tom';
		$this->logger = $this->getMockBuilder(
			'\OCA\News\Core\Logger')
			->disableOriginalConstructor()
			->getMock();
		$this->appName = 'news';
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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$this->request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);
		$this->msg = 'hohoho';
	}


	private function assertDefaultAnnotations($methodName){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, $methodName, $annotations);
	}


	public function testGetAllAnnotations(){
		$this->assertDefaultAnnotations('index');
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


	public function testFromUsersAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, 'fromAllUsers', $annotations);
	}


	public function testUpdateAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->feedAPI, 'update', $annotations);
	}


	public function testIndex() {
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

		$response = $this->feedAPI->index();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI()),
			'starredCount' => $starredCount,
			'newestItemId' => $newestItemId
		), $response->getData());
	}


	public function testIndexNoNewestItemId() {
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

		$response = $this->feedAPI->index();

		$this->assertEquals(array(
			'feeds' => array($feeds[0]->toAPI()),
			'starredCount' => $starredCount,
		), $response->getData());
	}


	public function testDelete() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2
		)));
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
		);

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
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->move();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testfromAllUsers(){
		$feed = new Feed();
		$feed->setUrl(3);
		$feed->setId(1);
		$feed->setUserId('john');
		$feeds = array($feed);
		$this->feedBusinessLayer->expects($this->once())
			->method('findAllFromAllUsers')
			->will($this->returnValue($feeds));
		$response = $this->feedAPI->fromAllUsers();
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
		$this->feedAPI = new FeedApiController(
			$this->appName,
			$request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->logger,
			$this->user
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
		$this->logger->expects($this->once())
			->method('log')
			->with($this->equalTo('Could not update feed ' . $this->msg),
				$this->equalTo('debug'));

		$response = $this->feedAPI->update();

		$this->assertTrue($response instanceof JSONResponse);

	}


}
