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


require_once(__DIR__ . "/../../classloader.php");


class UserSettingsControllerTest extends ControllerTestUtility {

	private $api;
	private $request;
	private $controller;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->request = new Request();
		$this->controller = new UserSettingsController($this->api, $this->request);
		$this->user = 'becka';
	}


	private function assertUserSettingsControllerAnnotations($methodName){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	public function testGetLanguageAnnotations(){
		$this->assertUserSettingsControllerAnnotations('getLanguage');
	}


	public function testFoldersAnnotations(){
		$this->assertUserSettingsControllerAnnotations('read');
	}


	public function testOpenAnnotations(){
		$this->assertUserSettingsControllerAnnotations('show');
	}


	public function testCollapseAnnotations(){
		$this->assertUserSettingsControllerAnnotations('hide');
	}


	public function testShow(){
		$this->api->expects($this->once())
			->method('setUserValue')
			->with($this->equalTo('showAll'), 
				$this->equalTo(true));
		$response = $this->controller->show();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testHide(){
		$this->api->expects($this->once())
			->method('setUserValue')
			->with($this->equalTo('showAll'), 
				$this->equalTo(false));
		$response = $this->controller->hide();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testRead(){
		$result = array(
			'showAll' => true
		);
		$this->api->expects($this->once())
			->method('getUserValue')
			->with($this->equalTo('showAll'))
			->will($this->returnValue('1'));
		
		$response = $this->controller->read();
		$this->assertEquals($result, $response->getParams());
		$this->assertTrue($response instanceof JSONResponse);
	}
	

	public function testGetLanguage(){
		$language = 'de';
		$lang = $this->getMock('Lang', array('findLanguage'));
		$lang->expects($this->once())
			->method('findLanguage')
			->will($this->returnValue($language));
		$this->api->expects($this->once())
			->method('getTrans')
			->will($this->returnValue($lang));

		$response = $this->controller->getLanguage();
		$params = $response->getParams();

		$this->assertEquals($language, $params['language']);
		$this->assertTrue($response instanceof JSONResponse);	
	}


}