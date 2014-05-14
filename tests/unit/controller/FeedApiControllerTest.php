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

use \OCP\AppFramework\Http;

use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class FeedApiControllerTest extends \PHPUnit_Framework_TestCase {

	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $feedAPI;
	private $appName;
	private $user;
	private $request;
	private $msg;
	private $logger;
	private $loggerParams;

	protected function setUp() {
		$this->user = 'tom';
		$this->loggerParams = array('hi');
		$this->logger = $this->getMockBuilder(
			'\OCP\ILogger')
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
			$this->user,
			$this->loggerParams
		);
		$this->msg = 'hohoho';
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
			'feeds' => $feeds,
			'starredCount' => $starredCount,
			'newestItemId' => $newestItemId
		), $response);
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
			'feeds' => $feeds,
			'starredCount' => $starredCount,
		), $response);
	}


	public function testDelete() {
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->with(
				$this->equalTo(2),
				$this->equalTo($this->user));

		$this->feedAPI->delete(2);
	}


	public function testDeleteDoesNotExist() {
		$this->feedBusinessLayer->expects($this->once())
			->method('delete')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->delete(2);

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testCreate() {
		$feeds = array(
			new Feed()
		);

		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with(
				$this->equalTo('url'),
				$this->equalTo(3),
				$this->equalTo($this->user))
			->will($this->returnValue($feeds[0]));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->returnValue(3));

		$response = $this->feedAPI->create('url', 3);

		$this->assertEquals(array(
			'feeds' => $feeds,
			'newestItemId' => 3
		), $response);
	}


	public function testCreateNoItems() {
		$feeds = array(
			new Feed()
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

		$response = $this->feedAPI->create('ho', 3);

		$this->assertEquals(array(
			'feeds' => $feeds
		), $response);
	}



	public function testCreateExists() {
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerConflictException($this->msg)));

		$response = $this->feedAPI->create('ho', 3);

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
	}


	public function testCreateError() {
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->create('ho', 3);

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testRead() {
		$this->itemBusinessLayer->expects($this->once())
			->method('readFeed')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$this->feedAPI->read(3, 30);
	}


	public function testMove() {
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$this->feedAPI->move(3, 30);
	}


	public function testMoveDoesNotExist() {
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->feedAPI->move(3, 4);

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testRename() {
		$feedId = 3;
		$feedTitle = 'test';

		$this->feedBusinessLayer->expects($this->once())
			->method('rename')
			->with(
				$this->equalTo($feedId),
				$this->equalTo($feedTitle),
				$this->equalTo($this->user));

		$this->feedAPI->rename($feedId, $feedTitle);
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
		$response = json_encode($this->feedAPI->fromAllUsers());
		$this->assertEquals('{"feeds":[{"id":1,"userId":"john"}]}', $response);
	}


	public function testUpdate() {
		$feedId = 3;
		$userId = 'hi';

		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo($feedId), $this->equalTo($userId));

		$this->feedAPI->update($userId, $feedId);
	}


	public function testUpdateError() {
		$feedId = 3;
		$userId = 'hi';
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->will($this->throwException(new \Exception($this->msg)));
		$this->logger->expects($this->once())
			->method('debug')
			->with($this->equalTo('Could not update feed ' . $this->msg),
				$this->equalTo($this->loggerParams));

		$this->feedAPI->update($userId, $feedId);


	}


}
