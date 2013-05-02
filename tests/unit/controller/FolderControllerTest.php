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

use \OCA\News\Db\Folder;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerExistsException;

require_once(__DIR__ . "/../../classloader.php");


class FolderControllerTest extends ControllerTestUtility {

	private $api;
	private $folderBusinessLayer;
	private $request;
	private $controller;
	private $msg;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->folderBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = new Request();
		$this->controller = new FolderController($this->api, $this->request,
				$this->folderBusinessLayer);
		$this->user = 'jack';
		$this->msg = 'ron';
	}


	private function assertFolderControllerAnnotations($methodName){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new FolderController($this->api, $request, $this->folderBusinessLayer);
	}

	public function testFoldersAnnotations(){
		$this->assertFolderControllerAnnotations('folders');
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


	public function testRenameAnnotations(){
		$this->assertFolderControllerAnnotations('rename');
	}


	
	public function testFolders(){
		$return = array(
			new Folder(),
			new Folder(),
		);
		$this->folderBusinessLayer->expects($this->once())
					->method('findAll')
					->will($this->returnValue($return));

		$response = $this->controller->folders();
		$expected = array(
			'folders' => $return
		);
		$this->assertEquals($expected, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testOpen(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->open();

		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCollapse(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
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

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('open')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->collapse();

		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCreate(){
		$post = array('folderName' => 'tech');
		$this->controller = $this->getPostController($post);
		$result = array(
			'folders' => array(new Folder())
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo($post['folderName']), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->create();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testCreateReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerExistsException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException($ex));

		$response = $this->controller->create();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testDelete(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('delete')
			->with($this->equalTo($url['folderId']), 
				$this->equalTo($this->user));
		
		$response = $this->controller->delete();

		$this->assertTrue($response instanceof JSONResponse);	
	}


	public function testDeleteDoesNotExist(){
		$url = array('folderId' => 5);
		$this->controller = $this->getPostController(array(), $url);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('delete')
			->will($this->throwException(new BusinessLayerException($this->msg)));
		
		$response = $this->controller->delete();

		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($this->msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRename(){
		$post = array('folderName' => 'tech');
		$url = array('folderId' => 4);
		$this->controller = $this->getPostController($post, $url);
		$result = array(
			'folders' => array(new Folder())
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo($url['folderId']),
				$this->equalTo($post['folderName']), 
				$this->equalTo($this->user))
			->will($this->returnValue($result['folders'][0]));
		
		$response = $this->controller->rename();

		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRenameReturnsErrorForInvalidCreate(){
		$msg = 'except';
		$ex = new BusinessLayerExistsException($msg);
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException($ex));

		$response = $this->controller->rename();
		$params = json_decode($response->render(), true);

		$this->assertEquals('error', $params['status']);
		$this->assertEquals($msg, $params['msg']);
		$this->assertTrue($response instanceof JSONResponse);
	}
}