<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    David Guillot <david@guillot.me>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2018 David Guillot
 */

namespace OCA\News\Controller;

use OCA\News\Service\UpdaterService;
use \OCP\IRequest;
use \OCP\IAppConfig;
use \OCP\IUserSession;

use \OCA\News\Service\StatusService;

class UtilityApiController extends ApiController
{

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        private UpdaterService $updaterService,
        private IAppConfig $settings,
        private StatusService $statusService
    ) {
        parent::__construct($request, $userSession);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function version(): array
    {
        $version = $this->settings->getValueString(
            $this->appName,
            'installed_version'
        );
        return ['version' => $version];
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function beforeUpdate(): void
    {
        $this->updaterService->beforeUpdate();
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function afterUpdate(): void
    {
        $this->updaterService->afterUpdate();
    }


    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function status(): array
    {
        return $this->statusService->getStatus();
    }
}
