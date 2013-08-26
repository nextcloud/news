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
use \OCA\AppFramework\Http\TextDownloadResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Utility\OPMLExporter;

require_once(__DIR__ . "/../../classloader.php");


class ExportControllerTest extends ControllerTestUtility {

	private $api;
	private $request;
	private $controller;
	private $user;
	private $feedBusinessLayer;
	private $folderBusinessLayer;
	private $opmlExporter;

	/**
	 * Gets run before each test
	 */
	public function setUp(){
		$this->api = $this->getAPIMock();
		$this->feedBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = $this->getMockBuilder('\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->request = new Request();
		$this->opmlExporter = new OPMLExporter();
		$this->controller = new ExportController($this->api, $this->request,
			$this->feedBusinessLayer, $this->folderBusinessLayer, $this->opmlExporter);
		$this->user = 'john';
	}


	public function testOpmlAnnotations(){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 
			'CSRFExemption');
		$this->assertAnnotations($this->controller, 'opml', $annotations);
	}


	public function testOpmlExportNoFeeds(){
		$opml = 
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
		"<opml version=\"2.0\">\n" .
		"  <head>\n" .
		"    <title>Subscriptions</title>\n" .
		"  </head>\n" .
		"  <body/>\n" .
		"</opml>\n";

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->feedBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array()));
		$this->folderBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue(array()));

		$return = $this->controller->opml();
		$this->assertTrue($return instanceof TextDownloadResponse);
		$this->assertEquals($opml, $return->render());
	}


}