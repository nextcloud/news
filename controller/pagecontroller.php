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

use \OCA\News\Core\Settings;

class PageController extends Controller {

	private $settings;
	private $l10n;

	public function __construct($appName, IRequest $request, Settings $settings,
		$l10n){
		parent::__construct($appName, $request);
		$this->settings = $settings;
		$this->l10n = $l10n;
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
		$showAll = $this->settings->getUserValue('showAll');
		$compact = $this->settings->getUserValue('compact');
		$language = $this->l10n->findLanguage();

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
			$this->settings->setUserValue('showAll', $isShowAll);
		}

		if($isCompact !== null) {
			$this->settings->setUserValue('compact', $isCompact);
		}

		return new JSONResponse();
	}

}