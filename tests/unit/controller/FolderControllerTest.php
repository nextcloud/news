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

use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;

require_once(__DIR__ . "/../../classloader.php");


class FolderControllerTest extends \PHPUnit_Framework_TestCase {

	private $appName;
	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $feedBusinessLayer;
	private $request;
	private $controller;
	private $msg;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->appName = 'news';
		$this->user = 'jack';
		$this->folderBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new FolderController($this->appName, $this->request,
				$this->folderBusinessLayer, 
				$this->feedBusinessLayer,
				$this->itemBusinessLayer,
				$this->user);
		$this->msg = 'ron';
	}


	
	public function testIndex(){
		$return = array(
			new Folder(),
			new Folder(),
		);
		$this->folderBusinessLayer->expects($this->once())
					->method('findAll')
					->will($this->returnValue($return));

		$response = $this->controller->index();
		$expected = array(
			'folders' => $return
		);
		$this->assertEquals($expected, $response);
	}


	public function testOpen(){
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->with($this->equalTo(3), 
				$this->equalTo(true), $this->equalTo($this->user));
		
		$this->controller->open(3);

	}


	public function testOpenDoesNotExist(){
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->open(5);

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testCollapse(){
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->with($this->equalTo(5), 
				$this->equalTo(false), $this->equalTo($this->user));
		
		$this->controller->collapse(5);

	}


	public function testCollapseDoesNotExist(){
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->collapse(5);

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testCreate(){
		$result = array(
			'folders' => array(new Folder())
		);

		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo('tech'), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->create('tech');

		$this->assertEquals($result, $response);
	}


	public function testCreateReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerValidationException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create('tech');
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY);
		$this->assertEquals($msg, $params['message']);
	}


	public function testCreateReturnsErrorForDuplicateCreate(){
		$msg = 'except';
		$ex = new BusinessLayerConflictException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create('tech');
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
		$this->assertEquals($msg, $params['message']);
	}


	public function testDelete(){
		$this->folderBusinessLayer->expects($this->once())
			->method('markDeleted')
			->with($this->equalTo(5), 
				$this->equalTo($this->user));
		
		$this->controller->delete(5);
	}


	public function testDeleteDoesNotExist(){
		$this->folderBusinessLayer->expects($this->once())
			->method('markDeleted')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->delete(5);

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['message']);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testRename(){
		$result = array(
			'folders' => array(new Folder())
		);

		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo(4),
				$this->equalTo('tech'), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->rename('tech', 4);

		$this->assertEquals($result, $response);
	}


	public function testRenameReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerValidationException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename('tech', 4);
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY);
		$this->assertEquals($msg, $params['message']);
	}


	public function testRenameDoesNotExist(){
		$msg = 'except';
		$ex = new BusinessLayerException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename('tech', 5);
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['message']);
	}


	public function testRenameReturnsErrorForDuplicateCreate(){
		$msg = 'except';
		$ex = new BusinessLayerConflictException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename('tech', 1);
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
		$this->assertEquals($msg, $params['message']);
	}


	
	public function testRead(){
		$feed = new Feed();
		$expected = array(
			'feeds' => array($feed)
		);

		$this->itemBusinessLayer->expects($this->once())
			->method('readFolder')
			->with($this->equalTo(4), 
				$this->equalTo(5), 
				$this->equalTo($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array($feed)));

		$response = $this->controller->read(4, 5);
		$this->assertEquals($expected, $response);
	}


	public function testRestore(){
		$this->folderBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->with($this->equalTo(5), 
				$this->equalTo($this->user));
		
		$this->controller->restore(5);
	}


	public function testRestoreDoesNotExist(){
		$this->folderBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->restore(5);

		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($this->msg, $params['message']);
	}

}