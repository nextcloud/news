<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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

namespace OCA\News\Controller;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\Bl\BLException;


require_once(__DIR__ . "/../../classloader.php");


class FeedControllerTest extends ControllerTestUtility {

	private $api;
	private $bl;
	private $request;
	private $controller;
	private $folderBl;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->bl = $this->getMockBuilder('\OCA\News\Bl\FeedBl')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBl = $this->getMockBuilder('\OCA\News\Bl\FolderBl')
			->disableOriginalConstructor()
			->getMock();
		$this->request = new Request();
		$this->controller = new FeedController($this->api, $this->request,
				$this->bl, $this->folderBl);
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
		return new FeedController($this->api, $request, $this->bl, $this->folderBl);
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


	public function testUpdateAnnotations(){
		$this->assertFeedControllerAnnotations('update');
	}


	public function testMoveAnnotations(){
		$this->assertFeedControllerAnnotations('move');
	}


	public function testFeeds(){
		$result = array(
			'feeds' => array(
				array('a feed')
			)
		);
		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->bl->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($result['feeds']));

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
		$ex = new BLException('hiu');
		$result = array(
			'activeFeed' => array(
				'id' => 0,
				'type' => FeedType::SUBSCRIPTIONS
			)
		);
		$this->bl->expects($this->once())
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
		$ex = new BLException('hiu');
		$result = array(
			'activeFeed' => array(
				'id' => 0,
				'type' => FeedType::SUBSCRIPTIONS
			)
		);
		$this->folderBl->expects($this->once())
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

		$this->bl->expects($this->once())
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
		$ex = new BLException($msg);
		$this->bl->expects($this->once())
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
		$this->bl->expects($this->once())
			->method('delete')
			->with($this->equalTo($url['feedId']));

		$response = $this->controller->delete();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUpdate(){
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
		$this->bl->expects($this->once())
			->method('update')
			->with($this->equalTo($url['feedId']), $this->equalTo($this->user))
			->will($this->returnValue($result['feeds'][0]));

		$response = $this->controller->update();

		$this->assertEquals($result, $response->getParams());
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
		$this->bl->expects($this->once())
			->method('move')
			->with($this->equalTo($url['feedId']), 
				$this->equalTo($post['parentFolderId']),
				$this->equalTo($this->user));

		$response = $this->controller->move();

		$this->assertTrue($response instanceof JSONResponse);
	}

}