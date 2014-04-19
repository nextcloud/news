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

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Utility\ControllerTestUtility;

require_once(__DIR__ . "/../../classloader.php");


class PageControllerTest extends ControllerTestUtility {

	private $settings;
	private $appName;
	private $request;
	private $controller;
	private $user;
	private $l10n;


	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->appName = 'news';
		$this->l10n = $this->getMock('L10N', array('findLanguage'));
		$this->settings = $this->getMockBuilder(
			'\OCA\News\Core\Settings')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getRequest();
		$this->controller = new PageController($this->appName, $this->request, 
			$this->settings, $this->l10n);
		$this->user = 'becka';
	}


	public function testIndexAnnotations(){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired');
		$this->assertAnnotations($this->controller, 'index', $annotations);
	}

	public function testSettingsAnnotations(){
		$annotations = array('NoAdminRequired');
		$this->assertAnnotations($this->controller, 'settings', $annotations);
	}

	public function testUpdateSettingsAnnotations(){
		$annotations = array('NoAdminRequired');
		$this->assertAnnotations($this->controller, 'updateSettings', $annotations);
	}

	public function testIndex(){
		$response = $this->controller->index();
		$this->assertEquals('main', $response->getTemplateName());
		$this->assertTrue($response instanceof TemplateResponse);
	}


	public function testSettings() {
		$result = array(
			'showAll' => true,
			'compact' => true,
			'language' => 'de'
		);

		$this->l10n->expects($this->once())
			->method('findLanguage')
			->will($this->returnValue('de'));
		$this->settings->expects($this->at(0))
			->method('getUserValue')
			->with($this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->settings->expects($this->at(1))
			->method('getUserValue')
			->with($this->equalTo('compact'))
			->will($this->returnValue('1'));

		$response = $this->controller->settings();
		$this->assertEquals($result, $response->getData());
		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUpdateSettings() {
		$request = $this->getRequest(array('post' => array(
			'showAll' => true,
			'compact' => true
		)));
		$this->controller = new PageController($this->appName, $request, 
			$this->settings, $this->l10n);

		$this->settings->expects($this->at(0))
			->method('setUserValue')
			->with($this->equalTo('showAll'), 
				$this->equalTo(true));
		$this->settings->expects($this->at(1))
			->method('setUserValue')
			->with($this->equalTo('compact'), 
				$this->equalTo(true));
		$response = $this->controller->updateSettings();

		$this->assertTrue($response instanceof JSONResponse);
	}


	public function testUpdateSettingsNoParameterShouldNotSetIt() {
		$request = $this->getRequest(array('post' => array(
			'showAll' => true
		)));
		$this->controller = new PageController($this->appName, $request, 
			$this->settings, $this->l10n);

		$this->settings->expects($this->once())
			->method('setUserValue')
			->with($this->equalTo('showAll'), 
				$this->equalTo(true));

		$response = $this->controller->updateSettings();

		$this->assertTrue($response instanceof JSONResponse);
	}
}