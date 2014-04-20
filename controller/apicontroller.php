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

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\IConfig;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\Response;

use \OCA\News\Utility\Updater;

class ApiController extends Controller {

	private $updater;
	private $settings;

	public function __construct($appName, IRequest $request, Updater $updater,
	                            IConfig $settings){
		parent::__construct($appName, $request);
		$this->updater = $updater;
		$this->settings = $settings;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @API
	 */
	public function version() {
		$version = $this->settings->getAppValue($this->appName,
			'installed_version');
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
