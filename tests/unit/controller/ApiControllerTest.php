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
use \OCP\AppFramework\Http\JSONResponse;


use \OCA\News\Utility\ControllerTestUtility;

require_once(__DIR__ . "/../../classloader.php");


class ApiControllerTest extends ControllerTestUtility {

	private $settings;
	private $request;
	private $newsAPI;
	private $updater;
	private $appName;

	protected function setUp() {
		$this->appName = 'news';
		$this->settings = $this->getMockBuilder(
			'\OCP\IConfig')
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
		$this->newsAPI = new ApiController($this->appName, $this->request, 
			$this->updater, $this->settings);
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
		$this->settings->expects($this->once())
			->method('getAppValue')
			->with($this->equalTo($this->appName),
				$this->equalTo('installed_version'))
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
		$this->newsAPI = new ApiController($this->appName, $this->request, 
			$this->updater, $this->settings);
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
		$this->newsAPI = new ApiController($this->appName, $this->request, 
			$this->updater, $this->settings);
		$response = $this->newsAPI->cors();

		$headers = $response->getHeaders();

		$this->assertEquals('test', $headers['Access-Control-Allow-Origin']);
	}


}
