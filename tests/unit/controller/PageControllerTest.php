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
		$this->user = 'becka';
		$this->l10n = $this->getMock('L10N', array('findLanguage'));
		$this->settings = $this->getMockBuilder(
			'\OCP\IConfig')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getRequest();
		$this->controller = new PageController($this->appName, $this->request, 
			$this->settings, $this->l10n, $this->user);
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
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'))
			->will($this->returnValue('1'));
		$this->settings->expects($this->at(1))
			->method('getUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('compact'))
			->will($this->returnValue('1'));

		$response = $this->controller->settings();
		$this->assertEquals($result, $response->getData());
	}


	public function testUpdateSettings() {
		$request = $this->getRequest(array('post' => array(
			'showAll' => true,
			'compact' => true
		)));
		$this->controller = new PageController($this->appName, $request, 
			$this->settings, $this->l10n, $this->user);

		$this->settings->expects($this->at(0))
			->method('setUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'), 
				$this->equalTo(true));
		$this->settings->expects($this->at(1))
			->method('setUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('compact'), 
				$this->equalTo(true));
		$this->controller->updateSettings();

	}


	public function testUpdateSettingsNoParameterShouldNotSetIt() {
		$request = $this->getRequest(array('post' => array(
			'showAll' => true
		)));
		$this->controller = new PageController($this->appName, $request, 
			$this->settings, $this->l10n, $this->user);

		$this->settings->expects($this->once())
			->method('setUserValue')
			->with($this->equalTo($this->user),
				$this->equalTo($this->appName),
				$this->equalTo('showAll'), 
				$this->equalTo(true));

		$this->controller->updateSettings();

	}
}