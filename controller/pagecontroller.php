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
use \OCP\IL10N;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Controller;


class PageController extends Controller {

	private $settings;
	private $l10n;
	private $userId;

	public function __construct($appName, 
	                            IRequest $request, 
	                            IConfig $settings,
	                            IL10N $l10n, 
	                            $userId){
		parent::__construct($appName, $request);
		$this->settings = $settings;
		$this->l10n = $l10n;
		$this->userId = $userId;
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
		$showAll = $this->settings->getUserValue($this->userId, $this->appName, 
			'showAll');
		$compact = $this->settings->getUserValue($this->userId, $this->appName, 
			'compact');
		$language = $this->l10n->findLanguage();

		return array(
			'showAll' => $showAll === '1',
			'compact' => $compact === '1',
			'language' => $language
		);
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param bool $showAll
	 * @param bool $compact
	 */
	public function updateSettings($showAll, $compact) {
		if($showAll !== null) {
			$this->settings->setUserValue($this->userId, $this->appName, 
				'showAll', $showAll);
		}

		if($compact !== null) {
			$this->settings->setUserValue($this->userId, $this->appName,
				'compact', $compact);
		}
	}

}