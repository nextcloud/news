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

namespace OCA\News\External;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\Response;
use \OCA\AppFramework\Http\Http;

use \OCA\News\Utility\Updater;


class NewsAPI extends Controller {

	private $updater;

	public function __construct(API $api, Request $request, Updater $updater){
		parent::__construct($api, $request);
		$this->updater = $updater;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function version() {
		$version = $this->api->getAppValue('installed_version');
		$response = new JSONResponse(array('version' => $version));
		return $response;
	}


	/**
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function beforeUpdate() {
		$this->updater->beforeUpdate();
		return new JSONResponse();
	}


	/**
	 * @CSRFExemption
	 * @Ajax
	 * @API
	 */
	public function afterUpdate() {
		$this->updater->afterUpdate();
		return new JSONResponse();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 * @IsLoggedInExemption
	 * @Ajax
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
