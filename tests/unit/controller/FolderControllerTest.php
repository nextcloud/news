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
use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerConflictException;
use \OCA\News\BusinessLayer\BusinessLayerValidationException;

require_once(__DIR__ . "/../../classloader.php");


class FolderControllerTest extends ControllerTestUtility {

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
		$this->request = $this->getRequest();
		$this->controller = new FolderController($this->appName, $this->request,
				$this->folderBusinessLayer, 
				$this->feedBusinessLayer,
				$this->itemBusinessLayer,
				$this->user);
		$this->msg = 'ron';
	}


	private function assertFolderControllerAnnotations($methodName){
		$annotations = array('NoAdminRequired');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new FolderController($this->appName, $request,
			$this->folderBusinessLayer, 
			$this->feedBusinessLayer,
			$this->itemBusinessLayer,
			$this->user);
	}

	public function testFoldersAnnotations(){
		$this->assertFolderControllerAnnotations('index');
	}


	public function testOpenAnnotations(){
		$this->assertFolderControllerAnnotations('open');
	}


	public function testCollapseAnnotations(){
		$this->assertFolderControllerAnnotations('collapse');
	}


	public function testCreateAnnotations(){
		$this->assertFolderControllerAnnotations('create');
	}


	public function testDeleteAnnotations(){
		$this->assertFolderControllerAnnotations('delete');
	}


	public function testRestoreAnnotations(){
		$this->assertFolderControllerAnnotations('restore');
	}


	public function testRenameAnnotations(){
		$this->assertFolderControllerAnnotations('rename');
	}


	public function testReadAnnotations(){
		$this->assertFolderControllerAnnotations('read');
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
		$this->assertEquals($expected, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testOpen(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo(true), $this->equalTo($this->user));
		
		$response = $this->controller->open();

		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testOpenDoesNotExist(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->open();

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testCollapse(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo(false), $this->equalTo($this->user));
		
		$response = $this->controller->collapse();

		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testCollapseDoesNotExist(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->collapse();

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testCreate(){
		$post = array('folderName' => 'tech');
		$this->controller = $this->getPostController($post);
		$result = array(
			'folders' => array(new Folder())
		);

		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo($post['folderName']), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->create();

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);	
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

		$response = $this->controller->create();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
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

		$response = $this->controller->create();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testDelete(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('markDeleted')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo($this->user));
		
		$response = $this->controller->delete();

		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testDeleteDoesNotExist(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('markDeleted')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->delete();

		$params = json_decode($response->render(), true);

		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
	}


	public function testRename(){
		$post = array('folderName' => 'tech');
		$url = array('folderId' => 4);
		$this->controller = $this->getPostController($post, $url);
		$result = array(
			'folders' => array(new Folder())
		);

		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo($url['folderId']),
				$this->equalTo($post['folderName']), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->rename();

		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRenameReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerValidationException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRenameDoesNotExist(){
		$msg = 'except';
		$ex = new BusinessLayerException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRenameReturnsErrorForDuplicateCreate(){
		$msg = 'except';
		$ex = new BusinessLayerConflictException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename();
		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	
	public function testRead(){
		$feed = new Feed();
		$url = array(
			'folderId' => 4
		);
		$post = array(
			'highestItemId' => 5
		);
		$this->controller = $this->getPostController($post, $url);
		$expected = array(
			'feeds' => array($feed)
		);

		$this->itemBusinessLayer->expects($this->once())
			->method('readFolder')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo($post['highestItemId']), 
				$this->equalTo($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array($feed)));

		$response = $this->controller->read();
		$this->assertTrue($response instanceof JSONResponse);
		$this->assertEquals($expected, $response->getData());
	}


	public function testRestore(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo($this->user));
		
		$response = $this->controller->restore();

		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testRestoreDoesNotExist(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->folderBusinessLayer->expects($this->once())
			->method('unmarkDeleted')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->restore();

		$params = json_decode($response->render(), true);

		$this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}

}