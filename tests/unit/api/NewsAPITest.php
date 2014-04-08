<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
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

namespace OCA\News\API;

use \OCP\IRequest;
use \OCP\AppFramework\Http\JSONResponse;


use \OCA\News\Utility\ControllerTestUtility;

require_once(__DIR__ . "/../../classloader.php");


class NewsAPITest extends ControllerTestUtility {

	private $api;
	private $request;
	private $newsAPI;
	private $updater;

	protected function setUp() {
		$this->api = $this->getMockBuilder(
			'\OCA\News\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->updater = $this->getMockBuilder(
			'\OCA\News\Utility\Updater')
			->disableOriginalConstructor()
			->getMock();
		$this->newsAPI = new NewsAPI($this->api, $this->request, $this->updater);
	}


	private function assertDefaultAnnotations($methodName){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired', 'API');
		$this->assertAnnotations($this->newsAPI, $methodName, $annotations);
	}

	public function testVersionAnnotations(){
		$this->assertDefaultAnnotations('version');
	}

	public function testBeforeUpdateAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->newsAPI, 'beforeUpdate', $annotations);
	}

	public function testAfterUpdateAnnotations(){
		$annotations = array('NoCSRFRequired', 'API');
		$this->assertAnnotations($this->newsAPI, 'afterUpdate', $annotations);
	}

	public function testGetVersion(){
		$this->api->expects($this->once())
			->method('getAppValue')
			->with($this->equalTo('installed_version'))
			->will($this->returnValue('1.0'));

		$response = $this->newsAPI->version();
		$data = $response->getData();
		$version = $data['version'];

		$this->assertEquals('1.0', $version);
	}


	public function testBeforeUpdate(){
		$this->updater->expects($this->once())
			->method('beforeUpdate');
		$response = $this->newsAPI->beforeUpdate();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testAfterUpdate(){
		$this->updater->expects($this->once())
			->method('afterUpdate');
		$response = $this->newsAPI->afterUpdate();
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testCorsAnnotations(){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired', 'PublicPage');
		$this->assertAnnotations($this->newsAPI, 'cors', $annotations);
	}


	public function testCors() {
		$this->request = $this->getRequest(array('server' => array()));
		$this->newsAPI = new NewsAPI($this->api, $this->request, $this->updater);
		$response = $this->newsAPI->cors();

		$headers = $response->getHeaders();

		$this->assertEquals('*', $headers['Access-Control-Allow-Origin']);
		$this->assertEquals('PUT, POST, GET, DELETE', $headers['Access-Control-Allow-Methods']);
		$this->assertEquals('true', $headers['Access-Control-Allow-Credentials']);
		$this->assertEquals('Authorization, Content-Type', $headers['Access-Control-Allow-Headers']);
		$this->assertEquals('1728000', $headers['Access-Control-Max-Age']);
	}


	public function testCorsUsesOriginIfGiven() {
		$this->request = $this->getRequest(array('server' => array('HTTP_ORIGIN' => 'test')));
		$this->newsAPI = new NewsAPI($this->api, $this->request, $this->updater);
		$response = $this->newsAPI->cors();

		$headers = $response->getHeaders();

		$this->assertEquals('test', $headers['Access-Control-Allow-Origin']);
	}


}
