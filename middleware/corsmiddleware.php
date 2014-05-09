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
use OCP\AppFramework\Middleware;

use OCA\News\Utility\MethodAnnotationReader;

class CORSMiddleware extends Middleware {

	private $request;

	public function __construct(IRequest $request) {
		$this->request = $request;
	}


	/**
	 * This is being run after a successful controllermethod call and allows
	 * the manipulation of a Response object. The middleware is run in reverse order
	 *
	 * @param Controller $controller the controller that is being called
	 * @param string $methodName the name of the method that will be called on
	 *                           the controller
	 * @param Response $response the generated response from the controller
	 * @return Response a Response object
	 */
	public function afterController($controller, $methodName, Response $response){
		$annotationReader = new MethodAnnotationReader($controller, $methodName);

		// only react if its an API request and if the request sends origin
		if(isset($this->request->server['HTTP_ORIGIN']) &&
			$annotationReader->hasAnnotation('API')) {

			$origin = $this->request->server['HTTP_ORIGIN'];
			$response->addHeader('Access-Control-Allow-Origin', $origin);
			$response->addHeader('Access-Control-Allow-Credentials', 'false');

		}
		return $response;
	}


}
