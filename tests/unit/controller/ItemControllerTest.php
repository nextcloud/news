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

use \OCA\News\Utility\ControllerTestUtility;
use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\BusinessLayer\BusinessLayerException;

require_once(__DIR__ . "/../../classloader.php");


class ItemControllerTest extends ControllerTestUtility {

	private $appName;
	private $settings;
	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $request;
	private $controller;
	private $newestItemId;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->appName = 'news';
		$this->user = 'jackob';
		$this->settings = $this->getMockBuilder(
			'\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = 
		$this->getMockBuilder('\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = 
		$this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getRequest();
		$this->controller = new ItemController($this->appName, $this->request,
				$this->feedBusinessLayer, $this->itemBusinessLayer, $this->settings,
				$this->user);
		$this->newestItemId = 12312;
	}

	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new ItemController($this->appName, $request,
			$this->feedBusinessLayer, $this->itemBusinessLayer, $this->settings,
				$this->user);
	}


	private function assertItemControllerAnnotations($methodName){
		$annotations = array('NoAdminRequired');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}

	
	public function testItemsAnnotations(){
		$this->assertItemControllerAnnotations('index');
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

		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->with($url['itemId'], true, $this->user);

		$this->controller->read();
	}


	public function testReadDoesNotExist(){
		$url = array(
			'itemId' => 4
		);
		$msg = 'hi';
		$this->controller = $this->getPostController(array(), $url);

		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($msg)));


		$response = $this->controller->read();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
	}


	public function testUnread(){
		$url = array(
			'itemId' => 4
		);
		$this->controller = $this->getPostController(array(), $url);

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

		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($msg)));


		$response = $this->controller->unread();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
	}


	public function testStar(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo($url['feedId']), 
				$this->equalTo($url['guidHash']),
				$this->equalTo(true), 
				$this->equalTo($this->user));

		$this->controller->star();
	}


	public function testStarDoesNotExist(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$msg = 'ho';
		$this->controller = $this->getPostController(array(), $url);

		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($msg)));;

		$response = $this->controller->star();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
	}


	public function testUnstar(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$this->controller = $this->getPostController(array(), $url);

		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo($url['feedId']), 
				$this->equalTo($url['guidHash']),
				$this->equalTo(false), 
				$this->equalTo($this->user));

		$this->controller->unstar();
	}


	public function testUnstarDoesNotExist(){
		$url = array(
			'feedId' => 4,
			'guidHash' => md5('test')
		);
		$msg = 'ho';
		$this->controller = $this->getPostController(array(), $url);

		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($msg)));;

		$response = $this->controller->unstar();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
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

		$this->itemBusinessLayer->expects($this->once())
			->method('readAll')
			->with($this->equalTo($post['highestItemId']), 
				$this->equalTo($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array($feed)));

		$response = $this->controller->readAll();
		$this->assertEquals($expected, $response->getData());
	}


	private function itemsApiExpects($id, $type){
		$this->settings->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->settings->expects($this->at(1))
			->method('setUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('lastViewedFeedId'),
				$this->equalTo($id));
		$this->settings->expects($this->at(2))
			->method('setUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('lastViewedFeedType'),
				$this->equalTo($type));
	}


	public function testIndex(){
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

		$response = $this->controller->index();
		$this->assertEquals($result, $response->getData());
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

		$response = $this->controller->index();
		$this->assertEquals($result, $response->getData());
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

		$response = $this->controller->index();
		$this->assertEquals($result, $response->getData());
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

		$this->settings->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'))
			->will($this->returnValue('1'));

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
	}


	public function testGetNewItemsNoNewestItemsId(){
		$result = array();
		$post = array(
			'lastModified' => 3,
			'type' => FeedType::FEED,
			'id' => 2
		);
		$this->controller = $this->getPostController($post);

		$this->settings->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'))
			->will($this->returnValue('1'));

		$this->itemBusinessLayer->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new BusinessLayerException('')));

		$response = $this->controller->newItems();
		$this->assertEquals($result, $response->getData());
	}


}