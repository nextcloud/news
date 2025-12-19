<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Controller;

use OCA\News\AppInfo\Application;
use OCA\News\Explore\Exceptions\RecommendedSiteNotFoundException;
use OCP\IRequest;
use OCP\IAppConfig;
use OCP\Util;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Services\IInitialState;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

use OCA\News\Service\StatusService;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Db\ListType;
use OCP\IUserSession;

class PageController extends Controller
{
    use JSONHttpErrorTrait;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        private IAppConfig $appConfig,
        private IConfig $config,
        private IURLGenerator $urlGenerator,
        private IL10N $l10n,
        private RecommendedSites $recommendedSites,
        private StatusService $statusService,
        private IInitialState $initialState
    ) {
        parent::__construct($request, $userSession);
    }

    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function index(): TemplateResponse
    {
        $status = $this->statusService->getStatus();

        // TODO: Remove check when dropping NC 30. Also consider dropping the reportUnmatchedIgnoredErrors line from
        // the phpstan config.
        if (class_exists('\OCP\ServerVersion')) {
            $version = (new \OCP\ServerVersion())->getMajorVersion();
        } else {
            /* @phpstan-ignore staticMethod.deprecated */
            $version = Util::getVersion()[0];
        }

        $response = new TemplateResponse(
            $this->appName,
            'index',
            [
                'nc_major_version' => $version,
                'warnings' => $status['warnings'],
                'url_generator' => $this->urlGenerator
            ]
        );

        $usersettings = [
            'preventReadOnScroll',
            'oldestFirst',
            'showAll',
            'lastViewedFeedId',
            'lastViewedFeedType',
            'disableRefresh',
            'displaymode',
            'splitmode'
        ];

        foreach ($usersettings as $setting) {
            $this->initialState->provideInitialState($setting, $this->config->getUserValue(
                $this->getUserId(),
                $this->appName,
                $setting,
                '0'
            ));
        }

        $exploreUrl = $this->appConfig->getValueString(
            $this->appName,
            'exploreUrl',
            Application::DEFAULT_SETTINGS['exploreUrl']
        );
        if (trim($exploreUrl) === '') {
                // default url should not feature the sites.en.json
                $exploreUrl = $this->urlGenerator->linkToRoute(
                    'news.page.explore',
                    ['lang' => 'en']
                );
                $exploreUrl = preg_replace('/feeds\.en\.json$/', '', $exploreUrl);
        }
        $this->initialState->provideInitialState("exploreUrl", $exploreUrl);

        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*')// chrome breaks on audio elements
            ->addAllowedFrameDomain('https://youtube.com')
            ->addAllowedFrameDomain('https://www.youtube.com')
            ->addAllowedFrameDomain('https://player.vimeo.com')
            ->addAllowedFrameDomain('https://www.player.vimeo.com')
            ->addAllowedFrameDomain('https://vk.com')
            ->addAllowedFrameDomain('https://www.vk.com');
        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * @param string $lang
     *
     * @return Http\JSONResponse|array
     */
    #[NoAdminRequired]
    public function explore(string $lang)
    {
        $this->config->setUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId',
            0
        );
        $this->config->setUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedType',
            ListType::EXPLORE
        );

        try {
            return $this->recommendedSites->forLanguage($lang);
        } catch (RecommendedSiteNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }
}
