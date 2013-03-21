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


require_once(__DIR__ . "/../classloader.php");


class FolderControllerTest extends ControllerTestUtility {

	private $api;
	private $folderBl;
	private $request;
	private $controller;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->folderBl = $this->getMockBuilder('\OCA\News\Bl\FolderBl')
			->disableOriginalConstructor()
			->getMock();
		$this->request = new Request();
		$this->controller = new FolderController($this->api, $this->request,
				$this->folderBl);
	}


	/**
	 * getAll
	 */
	public function testFoldersCalled(){
		$this->folderBl->expects($this->once())
					->method('findAll')
					->will($this->returnValue( array() ));
		
		$this->controller->folders();
	}


	public function testFoldersReturnsFolders(){
		$return = array(
			new Folder(),
			new Folder(),
		);
		$this->folderBl->expects($this->once())
					->method('findAll')
					->will($this->returnValue($return));

		$response = $this->controller->folders();
		$expected = array(
			'folders' => $return
		);
		$this->assertEquals($expected, $response->getParams());
	}

	
	public function testFoldersAnnotations(){
		$methodName = 'folders';
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');

		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	public function testFoldersReturnsJSON(){
		$response = $this->controller->folders();

		$this->assertTrue($response instanceof JSONResponse);
	}


	/**
	 * collapse
	 *//*
	public function testCollapseCalled(){
		$urlParams = array('folderId' => 1);
		$this->folderBl->expects($this->once())
					->method('setCollapsed')
					->with($this->equalTo($urlParams['folderId']), $this->equalTo(true));
		$this->controller->setURLParams($urlParams);
		
		$this->controller->collapse();
	}


	public function testCollapseReturnsNoParams(){
		$urlParams = array('folderId' => 1);
		$this->folderBl->expects($this->once())
					->method('setCollapsed')
					->with($this->equalTo($urlParams['folderId']), $this->equalTo(true));
		$this->controller->setURLParams($urlParams);

		$response = $this->controller->collapse();
		$this->assertEquals(array(), $response->getParams());
	}


	public function testCollapseAnnotations(){
		$methodName = 'collapse';
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');

		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	public function testCollapseReturnsJSON(){
		$urlParams = array('folderId' => 1);
		$this->folderBl->expects($this->once())
					->method('setCollapsed')
					->with($this->equalTo($urlParams['folderId']), $this->equalTo(true));
		$this->controller->setURLParams($urlParams);

		$response = $this->controller->collapse();

		$this->assertTrue($response instanceof JSONResponse);
	}


	private function collapseException($ex){
		$urlParams = array('folderId' => 1);
		$this->folderBl->expects($this->once())
					->method('setCollapsed')
					->with($this->equalTo($urlParams['folderId']), $this->equalTo(true))
					->will($this->throwException($ex));
		$this->controller->setURLParams($urlParams);

		$response = $this->controller->collapse();

		$expected = '{"status":"error","data":[],"msg":"' . $ex->getMessage() . '"}';
		$this->assertEquals($expected, $response->render());
	}
 

	public function testCollapseDoesNotExistExceptionReturnsJSONError(){
		$ex = new DoesNotExistException('exception');
		$this->collapseException($ex);
	}


	public function testCollapseMultipleObjectsReturnedReturnsJSONError(){
		$ex = new MultipleObjectsReturnedException('exception');
		$this->collapseException($ex);
	}
urlParams has been removed, please refactor*/

}