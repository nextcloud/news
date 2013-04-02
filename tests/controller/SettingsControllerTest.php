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
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;


require_once(__DIR__ . "/../classloader.php");


class SettingsControllerTest extends ControllerTestUtility {

	private $api;
	private $request;
	private $controller;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->request = new Request();
		$this->controller = new SettingsController($this->api, $this->request);
		$this->user = 'becka';
	}


	private function getPostController($postValue, $url=array()){
		$post = array(
			'post' => $postValue,
			'urlParams' => $url
		);

		$request = $this->getRequest($post);
		return new SettingsController($this->api, $request);
	}


	public function testIndexAnnotations(){
		$methodName = 'index';
		$annotations = array('CSRFExemption');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	public function testSetAutoPurgeLimitAnnotations(){
		$methodName = 'setAutoPurgeLimit';
		$annotations = array('Ajax');
		$this->assertAnnotations($this->controller, $methodName, $annotations);
	}


	public function testIndexReturnsAdminTemplate(){
		$this->api->expects($this->once())
			->method('getAppValue')
			->with($this->equalTo('purgeLimit'))
			->will($this->returnValue('30'));

		$response = $this->controller->index();
		$params = $response->getParams();

		$this->assertEquals('admin', $response->getTemplateName());
		$this->assertEquals(30, $params['purgeLimit']);
	}


	public function testIndexSetsLimitToNullIfValueIsNotSet(){
		$this->api->expects($this->once())
			->method('getAppValue')
			->with($this->equalTo('purgeLimit'))
			->will($this->returnValue(null));

		$response = $this->controller->index();
		$params = $response->getParams();

		$this->assertEquals(0, $params['purgeLimit']);
	}


	public function testSetAutoPurgeLimit(){
		$post = array(
			'purgeLimit' => '10'
		);
		$this->controller = $this->getPostController($post);

		$this->api->expects($this->once())
			->method('setAppValue')
			->with($this->equalTo('purgeLimit'), 
				$this->equalTo(10));	

		$response = $this->controller->setAutoPurgeLimit();			

		$this->assertTrue($response instanceof JSONResponse);
	}

}
