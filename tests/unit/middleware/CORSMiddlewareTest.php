<?php

/**
 * ownCloud - News
 *
 * @author Bernhard Posselt
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


namespace OCA\News\Middleware;

use OCA\AppFramework\Http\Request;
use OCA\AppFramework\Http\Response;

require_once(__DIR__ . "/../../classloader.php");


class CORSMiddlewareTest extends \PHPUnit_Framework_TestCase {


	/**
	 * @API
	 */
	public function testSetCORSAPIHeader() {
		$request = new Request(
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
		$request = new Request();
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
		$request = new Request();
		$middleware = new CORSMiddleware($request);
		$response = $middleware->afterController('\OCA\News\Middleware\CORSMiddlewareTest',
			'testNoOriginHeaderNoCORSHEADER',
			new Response());
		$headers = $response->getHeaders();
		$this->assertFalse(array_key_exists('Access-Control-Allow-Origin', $headers));
	}

}
