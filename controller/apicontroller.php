<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
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
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\Response;

use \OCA\News\Utility\Updater;
use \OCA\News\Core\API;

class ApiController extends Controller {

	private $updater;
	private $api;

	public function __construct(API $api, IRequest $request, Updater $updater){
		parent::__construct($api->getAppName(), $request);
		$this->updater = $updater;
		$this->api = $api;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function version() {
		$version = $this->api->getAppValue('installed_version');
		$response = new JSONResponse(array('version' => $version));
		return $response;
	}


	/**
	 * @NoCSRFRequired
	 * @API
	 */
	public function beforeUpdate() {
		$this->updater->beforeUpdate();
		return new JSONResponse();
	}


	/**
	 * @NoCSRFRequired
	 * @API
	 */
	public function afterUpdate() {
		$this->updater->afterUpdate();
		return new JSONResponse();
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function cors() {
		// needed for webapps access due to cross origin request policy
		if(isset($this->request->server['HTTP_ORIGIN'])) {
			$origin = $this->request->server['HTTP_ORIGIN'];
		} else {
			$origin = '*';
		}

		$response = new Response();
		$response->addHeader('Access-Control-Allow-Origin', $origin);
		$response->addHeader('Access-Control-Allow-Methods', 
			'PUT, POST, GET, DELETE');
		$response->addHeader('Access-Control-Allow-Credentials', 'true');
		$response->addHeader('Access-Control-Max-Age', '1728000');
		$response->addHeader('Access-Control-Allow-Headers', 
			'Authorization, Content-Type');
		return $response;
	}


}
