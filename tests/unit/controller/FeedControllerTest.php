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

use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;

require_once(__DIR__ . "/../../classloader.php");


class FeedControllerTest extends \PHPUnit_Framework_TestCase {

	private $appName;
	private $feedBusinessLayer;
	private $request;
	private $controller;
	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $settings;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->appName = 'news';
		$this->user = 'jack';
		$this->settings = $this->getMockBuilder(
			'\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new FeedController($this->appName, $this->request,
				$this->folderBusinessLayer,
				$this->feedBusinessLayer,
				$this->itemBusinessLayer,
				$this->settings,
				$this->user);
	}


	public function testIndex(){
		$result = array(
			'feeds' => array(
				array('a feed'),
			),
			'starred' => 13
		);
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['feeds']));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('')));
		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['starred']));

		$response = $this->controller->index();

		$this->assertEquals($result, $response);
	}


	public function testIndexHighestItemIdExists(){
		$result = array(
			'feeds' => array(
				array('a feed'),
			),
			'starred' => 13,
			'newestItemId' => 5
		);
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['feeds']));
		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['newestItemId']));
		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['starred']));

		$response = $this->controller->index();

		$this->assertEquals($result, $response);
	}



	private function activeInitMocks($id, $type){
		$this->settings->expects($this->at(0))
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('lastViewedFeedId'))
			->will($this->returnValue($id));
		$this->settings->expects($this->at(1))
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('lastViewedFeedType'))
			->will($this->returnValue($type));
	}


	public function testActive(){
		$id = 3;
		$type = FeedType::STARRED;
		$result = array(
			'activeFeed' => array(
				'id' => $id,
				'type' => $type
			)
		);

		$this->activeInitMocks($id, $type);

		$response = $this->controller->active();

		$this->assertEquals($result, $response);
	}


	public function testActiveFeedDoesNotExist(){
		$id = 3;
		$type = FeedType::FEED;
		$ex = new BusinessLayerException('hiu');
		$result = array(
			'activeFeed' => array(
				'id' => 0,
				'type' => FeedType::SUBSCRIPTIONS
			)
		);
		$this->feedBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->throwException($ex));

		$this->activeInitMocks($id, $type);

		$response = $this->controller->active();

		$this->assertEquals($result, $response);
	}


	public function testActiveFolderDoesNotExist(){
		$id = 3;
		$type = FeedType::FOLDER;
		$ex = new BusinessLayerException('hiu');
		$result = array(
			'activeFeed' => array(
				'id' => 0,
				'type' => FeedType::SUBSCRIPTIONS
			)
		);
		$this->folderBusinessLayer->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->throwException($ex));

		$this->activeInitMocks($id, $type);

		$response = $this->controller->active();

		$this->assertEquals($result, $response);
	}


	public function testActiveActiveIsNull(){
		$id = 3;
		$type = null;
		$result = array(
			'activeFeed' => array(
				'id' => 0,
				'type' => FeedType::SUBSCRIPTIONS
			)
		);

		$this->activeInitMocks($id, $type);

		$response = $this->controller->active();

		$this->assertEquals($result, $response);
	}


	public function testCreate(){
		$result = array(
			'feeds' => array(new Feed()),
			'newestItemId' => 3
		);

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->returnValue($result['newestItemId']));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo('hi'),
				$this->equalTo(4),
				$this->equalTo($this->user))
			->will($this->returnValue($result['feeds'][0]));

		$response = $this->controller->create('hi', 4);

		$this->assertEquals($result, $response);
	}


	public function testCreateNoItems(){
		$result = array(
			'feeds' => array(new Feed())
		);

		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->throwException(new BusinessLayerException('')));

		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo('hi'),
				$this->equalTo(4),
				$this->equalTo($this->user))
			->will($this->returnValue($result['feeds'][0]));

		$response = $this->controller->create('hi', 4);

		$this->assertEquals($result, $response);
	}


	public function testCreateReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerException($msg);
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create('hi', 4);
		$params = json_decode($response->render(), true);

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY);
	}


	public function testCreateReturnsErrorForDuplicateCreate(){
		$msg = 'except';
		$ex = new BusinessLayerConflictException($msg);
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create('hi', 4);
		$params = json_decode($response->render(), true);

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
	}


	public function testDelete(){
		$this->feedBusinessLayer->expects($this->once())
			->method('markDeleted')
			->with($this->equalTo(4));

		$this->controller->delete(4);
	}


	public function testDeleteDoesNotExist(){
		$msg = 'hehe';

		$this->feedBusinessLayer->expects($this->once())
			->method('markDeleted')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->delete(4);
		$params = json_decode($response->render(), true);

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testUpdate(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->setUnreadCount(44);
		$result = array(
			'feeds' => array(
				array(
					'id' => $feed->getId(),
					'unreadCount' => $feed->getUnreadCount()
				)
			)
		);

		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo(4), $this->equalTo($this->user))
			->will($this->returnValue($feed));

		$response = $this->controller->update(4);

		$this->assertEquals($result, $response);
	}


	public function testUpdateReturnsJSONError(){
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo(4), $this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('NO!')));

		$response = $this->controller->update(4);
		$render = $response->render();

		$this->assertEquals('{"message":"NO!"}', $render);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testMove(){
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->with($this->equalTo(4),
				$this->equalTo(3),
				$this->equalTo($this->user));

		$this->controller->move(4, 3);

	}


	public function testMoveDoesNotExist(){
		$msg = 'john';

		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->move(4, 3);
		$params = json_decode($response->render(), true);

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testRename(){
		$this->feedBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo(4),
				$this->equalTo('title'),
				$this->equalTo($this->user));

		$this->controller->rename(4, 'title');
	}


	public function testRenameDoesNotExist(){
		$msg = 'hi';

		$this->feedBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo(4),
				$this->equalTo('title'),
				$this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->rename(4, 'title');

		$params = $response->getData();

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testImport() {
		$feed = new Feed();

		$expected = array(
			'feeds' => array($feed)
		);

		$this->feedBusinessLayer->expects($this->once())
			->method('importArticles')
			->with($this->equalTo('json'),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));

		$response = $this->controller->import('json');

		$this->assertEquals($expected, $response);
	}


	public function testImportCreatesNoAdditionalFeed() {
		$this->feedBusinessLayer->expects($this->once())
			->method('importArticles')
			->with($this->equalTo('json'),
				$this->equalTo($this->user))
			->will($this->returnValue(null));

		$response = $this->controller->import('json');

		$this->assertEquals(array(), $response);
	}


	public function testReadFeed(){
		$expected = array(
			'feeds' => array(
				array(
					'id' => 4,
					'unreadCount' => 0
				)
			)
		);

		$this->itemBusinessLayer->expects($this->once())
			->method('readFeed')
			->with($this->equalTo(4), $this->equalTo(5), $this->user);

		$response = $this->controller->read(4, 5);
		$this->assertEquals($expected, $response);
	}


	public function testRestore() {
		$this->feedBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->with($this->equalTo(4));

		$this->controller->restore(4);
	}


	public function testRestoreDoesNotExist(){
		$msg = 'hehe';

		$this->feedBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->restore(4);
		$params = json_decode($response->render(), true);

		$this->assertEquals($msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}

}
