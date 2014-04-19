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


namespace OCA\News\Http;


require_once(__DIR__ . "/../../classloader.php");


class ChildDownloadResponse extends DownloadResponse {};


class DownloadResponseTest extends \PHPUnit_Framework_TestCase {

	protected $response;

	protected function setUp(){
		$this->response = new ChildDownloadResponse('file', 'content');
	}


	public function testHeaders() {
		$headers = $this->response->getHeaders();

		$this->assertContains('attachment; filename="file"', $headers['Content-Disposition']);
		$this->assertContains('content', $headers['Content-Type']);
	}


}