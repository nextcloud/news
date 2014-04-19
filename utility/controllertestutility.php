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


namespace OCA\News\Utility;

use OCP\IRequest;
use OCP\AppFramework\Http\Response;


/**
 * Simple utility class for testing controllers
 */
abstract class ControllerTestUtility extends \PHPUnit_Framework_TestCase {


	/**
	 * Checks if a controllermethod has the expected annotations
	 * @param Controller/string $controller name or instance of the controller
	 * @param array $expected an array containing the expected annotations
	 * @param array $valid if you define your own annotations, pass them here
	 */
	protected function assertAnnotations($controller, $method, array $expected,
										array $valid=array()){
		$standard = array(
			'PublicPage',
			'NoAdminRequired',
			'NoCSRFRequired',
			'API'
		);

		$possible = array_merge($standard, $valid);

		// check if expected annotations are valid
		foreach($expected as $annotation){
			$this->assertTrue(in_array($annotation, $possible));
		}

		$reader = new MethodAnnotationReader($controller, $method);
		foreach($expected as $annotation){
			$this->assertTrue($reader->hasAnnotation($annotation));
		}
	}


	/**
	 * Shortcut for testing expected headers of a response
	 * @param array $expected an array with the expected headers
	 * @param Response $response the response which we want to test for headers
	 */
	protected function assertHeaders(array $expected=array(), Response $response){
		$headers = $reponse->getHeaders();
		foreach($expected as $header){
			$this->assertTrue(in_array($header, $headers));
		}
	}


	/**
	 * Instead of using positional parameters this function instantiates
	 * a request by using a hashmap so its easier to only set specific params
	 * @param array $params a hashmap with the parameters for request
	 * @return Request a request instance
	 */
	protected function getRequest(array $params=array()) {
		$mock = $this->getMockBuilder('\OCP\IRequest')
			->getMock();

		$merged = array();

		foreach ($params as $key => $value) {
			$merged = array_merge($value, $merged);
		}

		$mock->expects($this->any())
			->method('getParam')
			->will($this->returnCallback(function($index, $default) use ($merged) {
				if (array_key_exists($index, $merged)) {
					return $merged[$index];
				} else {
					return $default;
				}
			}));

		// attribute access
		if(array_key_exists('server', $params)) {
			$mock->server = $params['server'];
		}

		return $mock;
	}

}
