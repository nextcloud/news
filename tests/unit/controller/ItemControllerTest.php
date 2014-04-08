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

use \OCP\IRequest;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Utility\ControllerTestUtility;
use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\BusinessLayer\BusinessLayerException;

require_once(__DIR__ . "/../../classloader.php");


class ItemControllerTest extends ControllerTestUtility {

	private $api;
	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $request;
	private $controller;
	private $newestItemId;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->itemBusinessLayer = 
		$this->getMockBuilder('\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = 
		$this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getRequest();
		$this->controller = new ItemController($this->api, $this->request,
				$this->feedBusinessLayer, $this->itemBusinessLayer);
		$this->user = 'jackob';
		$this->newestItemId = 12312;
	}

	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new ItemController($this->api, $request,
			$this->feedBusinessLayer, $this->itemBusinessLayer);
	}


	private function assertItemControllerAnnotations($methodName){
		$annotations = array('NoAdminRequired');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}

	
	public function testItemsAnnotations(){
		$this->assertItemControllerAnnotations('items');
	}


	public function testNewItemsAnnotations(){
		$this->assertItemControllerAnnotations('newItems');
	}

	public function testStarAnnotations(){
		$this->assertItemControllerAnnotations('star');
	}


	public function testUnstarAnnotations(){
		$this->assertItemControllerAnnotations('unstar');
	}


	public function testReadAnnotations(){
		$this->assertItemControllerAnnotations('read');
	}


	public function testUnreadAnnotations(){
		$this->assertItemControllerAnnotations('unread');
	}

	public function testReadAllAnnotations(){
		$this->assertItemControllerAnnotations('readAll');
	}


	public function testRead(){
		$url = array(
			'itemId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->with($url['itemId'], true, $this->user);


		$result = $this->controller->read();
		$this->assertTrue($result instanceof JSONResponse);
	}


	public function testReadDoesNotExist(){
		$url = array(
			'itemId' => 4
		);
		$msg = 'hi';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($msg)));


		$response = $this->controller->read();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUnread(){
		$url = array(
			'itemId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->with($url['itemId'], false, $this->user);

		$this->controller->unread();
	}



	public function testUnreadDoesNotExist(){
		$url = array(
			'itemId' => 4
		);
		$msg = 'hi';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($msg)));


		$response = $this->controller->unread();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testStar(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo($url['feedId']), 
				$this->equalTo($url['guidHash']),
				$this->equalTo(true), 
				$this->equalTo($this->user));

		$response = $this->controller->star();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testStarDoesNotExist(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$msg = 'ho';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($msg)));;

		$response = $this->controller->star();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUnstar(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo($url['feedId']), 
				$this->equalTo($url['guidHash']),
				$this->equalTo(false), 
				$this->equalTo($this->user));

		$response = $this->controller->unstar();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUnstarDoesNotExist(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$msg = 'ho';
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($msg)));;

		$response = $this->controller->unstar();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testReadAll(){
		$feed = new Feed();
		$post = array(
			'highestItemId' => 5
		);
		$this->controller = $this->getPostController($post);
		$expected = array(
			'feeds' => array($feed)
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('readAll')
			->with($this->equalTo($post['highestItemId']), 
				$this->equalTo($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array($feed)));

		$response = $this->controller->readAll();
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($expected, $response->getData());
	}


	private function itemsApiExpects($id, $type){
		$this->api->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->api->expects($this->at(2))
			->method('setUserValue')
			->with($this->equalTo('lastViewedFeedId'),
				$this->equalTo($id));
		$this->api->expects($this->at(3))
			->method('setUserValue')
			->with($this->equalTo('lastViewedFeedType'),
				$this->equalTo($type));
	}


	public function testItems(){
		$feeds = array(new Feed());
		$result = array(
			'items' => array(new Item()),
			'feeds' => $feeds,
			'newestItemId' => $this->newestItemId,
			'starred' => 3111
		);
		$post = array(
			'limit' => 3,
			'type' => FeedType::FEED,
			'id' => 2,
			'offset' => 0,
		);
		$this->controller = $this->getPostController($post);

		$this->itemsApiExpects($post['id'], $post['type']);

		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->newestItemId));

		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue(3111));

		$this->itemBusinessLayer->expects($this->once())
			->method('findAll')
			->with(
				$this->equalTo($post['id']), 
				$this->equalTo($post['type']), 
				$this->equalTo($post['limit']), 
				$this->equalTo($post['offset']),
				$this->equalTo(true), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['items']));

		$response = $this->controller->items();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testItemsOffsetNotZero(){
		$result = array(
			'items' => array(new Item())
		);
		$post = array(
			'limit' => 3,
			'type' => FeedType::FEED,
			'id' => 2,
			'offset' => 10,
		);
		$this->controller = $this->getPostController($post);

		$this->itemsApiExpects($post['id'], $post['type']);

		$this->itemBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($post['id']), 
				$this->equalTo($post['type']), 
				$this->equalTo($post['limit']), 
				$this->equalTo($post['offset']),
				$this->equalTo(true), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['items']));

		$this->feedBusinessLayer->expects($this->never())
			->method('findAll');

		$response = $this->controller->items();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testGetItemsNoNewestItemsId(){
		$result = array();
		$post = array(
			'limit' => 3,
			'type' => FeedType::FEED,
			'id' => 2,
			'offset' => 0,
			'newestItemId' => 3 
		);
		$this->controller = $this->getPostController($post);

		$this->itemsApiExpects($post['id'], $post['type']);

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('')));

		$response = $this->controller->items();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);			
	}


	public function testNewItems(){
		$feeds = array(new Feed());
		$result = array(
			'items' => array(new Item()),
			'feeds' => $feeds,
			'newestItemId' => $this->newestItemId,
			'starred' => 3111
		);
		$post = array(
			'lastModified' => 3,
			'type' => FeedType::FEED,
			'id' => 2
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));

		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->newestItemId));

		$this->itemBusinessLayer->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue(3111));

		$this->itemBusinessLayer->expects($this->once())
			->method('findAllNew')
			->with(
				$this->equalTo($post['id']), 
				$this->equalTo($post['type']), 
				$this->equalTo($post['lastModified']),
				$this->equalTo(true), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['items']));

		$response = $this->controller->newItems();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testGetNewItemsNoNewestItemsId(){
		$result = array();
		$post = array(
			'lastModified' => 3,
			'type' => FeedType::FEED,
			'id' => 2
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('')));

		$response = $this->controller->newItems();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);			
	}


}