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
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Controller;

use \OCA\News\Core\API;

class PageController extends Controller {

	private $api;

	public function __construct(API $api, IRequest $request){
		parent::__construct($api->getAppName(), $request);
		$this->api = $api;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return $this->render('main');
	}


	/**
	 * @NoAdminRequired
	 */
	public function settings() {
		$showAll = $this->api->getUserValue('showAll');
		$compact = $this->api->getUserValue('compact');
		$language = $this->api->getTrans()->findLanguage();

		$settings = array(
			'showAll' => $showAll === '1',
			'compact' => $compact === '1',
			'language' => $language
		);

		return new JSONResponse($settings);
	}


	/**
	 * @NoAdminRequired
	 */
	public function updateSettings() {
		$isShowAll = $this->params('showAll', null);
		$isCompact = $this->params('compact', null);
		
		if($isShowAll !== null) {
			$this->api->setUserValue('showAll', $isShowAll);
		}

		if($isCompact !== null) {
			$this->api->setUserValue('compact', $isCompact);
		}

		return new JSONResponse();
	}

}