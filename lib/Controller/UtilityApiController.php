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
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\OpenAPI;

use \OCA\News\Service\StatusService;

#[OpenAPI(scope: OpenAPI::SCOPE_DEFAULT)]
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


    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    #[FrontpageRoute(verb: 'GET', url: '/api/{apiVersion}/version', requirements: ['apiVersion' => 'v(1-[23]|2)'])]
    public function version(): array
    {
        $version = $this->settings->getValueString(
            $this->appName,
            'installed_version'
        );
        return ['version' => $version];
    }


    #[CORS]
    #[NoCSRFRequired]
    #[FrontpageRoute(verb: 'GET', url: '/api/{apiVersion}/cleanup/before-update', requirements: ['apiVersion' => 'v1-[23]'])]
    public function beforeUpdate(): void
    {
        $this->updaterService->beforeUpdate();
    }

    #[CORS]
    #[NoCSRFRequired]
    #[FrontpageRoute(verb: 'GET', url: '/api/{apiVersion}/cleanup/after-update', requirements: ['apiVersion' => 'v1-[23]'])]
    public function afterUpdate(): void
    {
        $this->updaterService->afterUpdate();
    }

    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    #[FrontpageRoute(verb: 'GET', url: '/api/{apiVersion}/status', requirements: ['apiVersion' => 'v1-[23]'])]
    public function status(): array
    {
        return $this->statusService->getStatus();
    }
}
