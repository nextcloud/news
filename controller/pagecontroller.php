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