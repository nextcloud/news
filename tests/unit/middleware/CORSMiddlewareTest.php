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


namespace OCA\News\Middleware;

use OCP\IRequest;
use OCP\AppFramework\Http\Response;

use OCA\News\Utility\ControllerTestUtility;

require_once(__DIR__ . "/../../classloader.php");


class CORSMiddlewareTest extends ControllerTestUtility {


	/**
	 * @API
	 */
	public function testSetCORSAPIHeader() {
		$request = $this->getRequest(
			array('server' => array('HTTP_ORIGIN' => 'test'))
		);
		$middleware = new CORSMiddleware($request);
		$response = $middleware->afterController('\OCA\News\Middleware\CORSMiddlewareTest',
			'testSetCORSAPIHeader',
			new Response());
		$headers = $response->getHeaders();

		$this->assertEquals('test', $headers['Access-Control-Allow-Origin']);
	}


	public function testNoAPINoCORSHEADER() {
		$request = $this->getRequest();
		$middleware = new CORSMiddleware($request);
		$response = $middleware->afterController('\OCA\News\Middleware\CORSMiddlewareTest',
			'testNoAPINoCORSHEADER',
			new Response());
		$headers = $response->getHeaders();
		$this->assertFalse(array_key_exists('Access-Control-Allow-Origin', $headers));
	}


	/**
	 * @API
	 */
	public function testNoOriginHeaderNoCORSHEADER() {
		$request = $this->getRequest();
		$middleware = new CORSMiddleware($request);
		$response = $middleware->afterController('\OCA\News\Middleware\CORSMiddlewareTest',
			'testNoOriginHeaderNoCORSHEADER',
			new Response());
		$headers = $response->getHeaders();
		$this->assertFalse(array_key_exists('Access-Control-Allow-Origin', $headers));
	}

}
