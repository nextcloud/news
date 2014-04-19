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


class TextResponseTest extends \PHPUnit_Framework_TestCase {


	protected function setUp() {
		$this->response = new TextResponse('sometext');
	}


	public function testRender() {
		$this->assertEquals('sometext', $this->response->render());
	}

	public function testContentTypeDefaultsToText(){
		$headers = $this->response->getHeaders();

		$this->assertEquals('text/plain', $headers['Content-type']);
	}


	public function testContentTypeIsSetableViaConstructor(){
		$response = new TextResponse('sometext', 'html');
		$headers = $response->getHeaders();

		$this->assertEquals('text/html', $headers['Content-type']);
	}

}