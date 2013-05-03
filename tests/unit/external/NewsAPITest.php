<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
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

namespace OCA\News\External;


require_once(__DIR__ . "/../../classloader.php");


class NewsAPITest extends \PHPUnit_Framework_TestCase {

	private $api;
	private $request;
	private $newsAPI;

	protected function setUp() {
		$this->api = $this->getMockBuilder(
			'\OCA\AppFramework\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCA\AppFramework\Http\Request')
			->disableOriginalConstructor()
			->getMock();
		$this->newsAPI = new NewsAPI($this->api, $this->request);
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


}