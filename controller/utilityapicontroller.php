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
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http;

use \OCA\News\Utility\Updater;


class UtilityApiController extends ApiController {

	private $updater;
	private $settings;

	public function __construct($appName,
	                            IRequest $request,
	                            Updater $updater,
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
		return ['version' => $version];
	}


	/**
	 * @NoCSRFRequired
	 */
	public function beforeUpdate() {
		$this->updater->beforeUpdate();
	}


	/**
	 * @NoCSRFRequired
	 */
	public function afterUpdate() {
		$this->updater->afterUpdate();
	}


}
