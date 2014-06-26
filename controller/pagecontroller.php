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

use OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\IConfig;
use \OCP\IL10N;
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
		return new TemplateResponse($this->appName, 'main');
	}


	/**
	 * @NoAdminRequired
	 */
	public function settings() {
		$settings = ['showAll', 'compact', 'preventReadOnScroll', 'oldestFirst'];

		$result = ['language' => $this->l10n->getLanguageCode()];

		foreach ($settings as $setting) {
			$result[$setting] = $this->settings->getUserValue(
				$this->userId, $this->appName, $setting
			) === '1';
		}
		return ['settings' => $result];
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param bool $showAll
	 * @param bool $compact
	 * @param bool $preventReadOnScroll
	 * @param bool $oldestFirst
	 */
	public function updateSettings($showAll, $compact, $preventReadOnScroll, $oldestFirst) {
		$settings = ['showAll', 'compact', 'preventReadOnScroll', 'oldestFirst'];
		
		foreach ($settings as $setting) {
			$this->settings->setUserValue($this->userId, $this->appName, 
			                              $setting, ${$setting});
		}
	}


}