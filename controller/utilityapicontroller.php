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
use \OCA\News\Service\StatusService;


class UtilityApiController extends ApiController {

    private $updater;
    private $settings;
    private $statusService;

    public function __construct($AppName,
                                IRequest $request,
                                Updater $updater,
                                IConfig $settings,
                                StatusService $statusService){
        parent::__construct($AppName, $request);
        $this->updater = $updater;
        $this->settings = $settings;
        $this->statusService = $statusService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function version() {
        $version = $this->settings->getAppValue($this->appName,
            'installed_version');
        return ['version' => $version];
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function beforeUpdate() {
        $this->updater->beforeUpdate();
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function afterUpdate() {
        $this->updater->afterUpdate();
    }


    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function status() {
        return $this->statusService->getStatus();
    }


}
