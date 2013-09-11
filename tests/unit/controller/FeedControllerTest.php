<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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

namespace OCA\News\Controller;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\BusinessLayer\BusinessLayerException;


require_once(__DIR__ . "/../../classloader.php");


class FeedControllerTest extends ControllerTestUtility {

	private $api;
	private $feedBusinessLayer;
	private $request;
	private $controller;
	private $folderBusinessLayer;
	private $itemBusinessLayer;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->itemBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = new Request();
		$this->controller = new FeedController($this->api, $this->request,
				$this->folderBusinessLayer,
				$this->feedBusinessLayer,
				$this->itemBusinessLayer);
		$this->user = 'jack';
	}

	private function assertFeedControllerAnnotations($methodName){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new FeedController($this->api, $request,
			$this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer);
	}


	public function testFeedsAnnotations(){
		$this->assertFeedControllerAnnotations('feeds');
	}


	public function testActiveAnnotations(){
		$this->assertFeedControllerAnnotations('active');
	}


	public function testCreateAnnotations(){
		$this->assertFeedControllerAnnotations('create');
	}


	public function testDeleteAnnotations(){
		$this->assertFeedControllerAnnotations('delete');
	}


	public function testRestoreAnnotations(){
		$this->assertFeedControllerAnnotations('restore');
	}


	public function testUpdateAnnotations(){
		$this->assertFeedControllerAnnotations('update');
	}


	public function testMoveAnnotations(){
		$this->assertFeedControllerAnnotations('move');
	}


	public function testImportArticlesAnnotations(){
		$this->assertFeedControllerAnnotations('importArticles');
	}

	public function testReadAnnotations(){
		$this->assertFeedControllerAnnotations('read');
	}

	public function testFeeds(){
		$result = array(
			'feeds' => array(
				array('a feed'),
			),
			'starred' => 13
		);
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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

		$response = $this->controller->feeds();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testFeedsHighestItemIdExists(){
		$result = array(
			'feeds' => array(
				array('a feed'),
			),
			'starred' => 13,
			'newestItemId' => 5
		);
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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

		$response = $this->controller->feeds();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}



	private function activeInitMocks($id, $type){
		$this->api->expects($this->at(0))
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->api->expects($this->at(1))
			->method('getUserValue')
			->with($this->equalTo('lastViewedFeedId'))
			->will($this->returnValue($id));
		$this->api->expects($this->at(2))
			->method('getUserValue')
			->with($this->equalTo('lastViewedFeedType'))
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

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
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

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
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

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
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

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCreate(){
		$result = array(
			'feeds' => array(new Feed()),
			'newestItemId' => 3
		);

		$post = array(
			'url' => 'hi',
			'parentFolderId' => 4
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->returnValue($result['newestItemId']));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo($post['url']),
				$this->equalTo($post['parentFolderId']),
				$this->equalTo($this->user))
			->will($this->returnValue($result['feeds'][0]));

		$response = $this->controller->create();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCreateNoItems(){
		$result = array(
			'feeds' => array(new Feed())
		);

		$post = array(
			'url' => 'hi',
			'parentFolderId' => 4
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->will($this->throwException(new BusinessLayerException('')));

		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo($post['url']),
				$this->equalTo($post['parentFolderId']),
				$this->equalTo($this->user))
			->will($this->returnValue($result['feeds'][0]));

		$response = $this->controller->create();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCreateReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerException($msg);
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->feedBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testDelete(){
		$url = array(
				'feedId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('markDeleted')
			->with($this->equalTo($url['feedId']));

		$response = $this->controller->delete();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testDeleteDoesNotExist(){
		$url = array(
				'feedId' => 4
		);
		$msg = 'hehe';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('markDeleted')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->delete();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
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

		$url = array(
			'feedId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo($url['feedId']), $this->equalTo($this->user))
			->will($this->returnValue($feed));

		$response = $this->controller->update();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUpdateReturnsJSONError(){
		$result = array(
			'feeds' => array(
				new Feed()
			)
		);

		$url = array(
				'feedId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('update')
			->with($this->equalTo($url['feedId']), $this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('NO!')));

		$response = $this->controller->update();
		$render = $response->render();

		$this->assertEquals('{"data":[],"status":"error","msg":"NO!"}', $render);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testMove(){
		$post = array(
			'parentFolderId' => 3
		);
		$url = array(
			'feedId' => 4
		);
		$this->controller = $this->getPostController($post, $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->with($this->equalTo($url['feedId']),
				$this->equalTo($post['parentFolderId']),
				$this->equalTo($this->user));

		$response = $this->controller->move();

		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testMoveDoesNotExist(){
		$post = array(
			'parentFolderId' => 3
		);
		$url = array(
			'feedId' => 4
		);
		$msg = 'john';
		$this->controller = $this->getPostController($post, $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('move')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->move();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testImportArticles() {
		$feed = new Feed();

		$post = array(
			'json' => 'the json'
		);
		$expected = array(
			'feeds' => array($feed)
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('importArticles')
			->with($this->equalTo($post['json']),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));

		$response = $this->controller->importArticles();

		$this->assertEquals($expected, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testImportArticlesCreatesNoAdditionalFeed() {
		$feed = new Feed();

		$post = array(
			'json' => 'the json'
		);
		$expected = array();
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('importArticles')
			->with($this->equalTo($post['json']),
				$this->equalTo($this->user))
			->will($this->returnValue(null));

		$response = $this->controller->importArticles();

		$this->assertEquals($expected, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testReadFeed(){
		$url = array(
			'feedId' => 4
		);
		$post = array(
			'highestItemId' => 5
		);
		$this->controller = $this->getPostController($post, $url);
		$expected = array(
			'feeds' => array(
				array(
					'id' => 4,
					'unreadCount' => 0
				)
			)
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('readFeed')
			->with($url['feedId'], $post['highestItemId'], $this->user);

		$response = $this->controller->read();
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($expected, $response->getParams());
	}


	public function testRestore() {
		$url = array(
				'feedId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->with($this->equalTo($url['feedId']));

		$response = $this->controller->restore();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRestoreDoesNotExist(){
		$url = array(
				'feedId' => 4
		);
		$msg = 'hehe';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->will($this->throwException(new BusinessLayerException($msg)));

		$response = $this->controller->restore();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}

}
