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
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Services\IInitialState;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\Config\IUserConfig;

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
        private IUserConfig $userConfig,
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
    #[FrontpageRoute(verb: 'GET', url: '/')]
    #[FrontpageRoute(verb: 'GET', url: '/all', postfix: 'view.all')]
    #[FrontpageRoute(verb: 'GET', url: '/item/{itemId}', postfix: 'view.itemid')]
    #[FrontpageRoute(verb: 'GET', url: '/feed/{feedId}', postfix: 'view.feedid')]
    #[FrontpageRoute(verb: 'GET', url: '/folder/{folderId}', postfix: 'view.folderid')]
    #[FrontpageRoute(verb: 'GET', url: '/recent', postfix: 'view.recent')]
    #[FrontpageRoute(verb: 'GET', url: '/starred', postfix: 'view.starred')]
    #[FrontpageRoute(verb: 'GET', url: '/starred/{starredFeedId}', postfix: 'view.starred.feed')]
    #[FrontpageRoute(verb: 'GET', url: '/unread', postfix: 'view.unread')]
    #[FrontpageRoute(verb: 'GET', url: '/explore', postfix: 'view.explore')]
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

        $userSettingsString = [
            'preventReadOnScroll',
            'oldestFirst',
            'showAll',
            'disableRefresh',
            'displaymode',
            'splitmode',
            'starredOpenState'
        ];

        foreach ($userSettingsString as $setting) {
            $this->initialState->provideInitialState($setting, $this->userConfig->getValueString(
                $this->getUserId(),
                $this->appName,
                $setting,
                '0'
            ));
        }

        $this->initialState->provideInitialState('mediaOptions', $this->userConfig->getValueString(
            $this->getUserId(),
            $this->appName,
            'mediaOptions',
            '{}'
        ));

        $this->initialState->provideInitialState('lastViewedFeedId', (string) $this->userConfig->getValueInt(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId',
            0
        ));

        $this->initialState->provideInitialState('lastViewedFeedType', (string) $this->userConfig->getValueInt(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedType',
            ListType::UNREAD
        ));

        $exploreUrl = $this->appConfig->getValueString(
            $this->appName,
            'exploreUrl',
            Application::DEFAULT_SETTINGS['exploreUrl']
        );

        // Generate default URL - get the explore API endpoint and strip the filename
        $defaultExploreUrl = $this->urlGenerator->linkToRoute('news.page.explore', ['lang' => 'en']);
        // Remove just a trailing filename, keeping the /explore/ directory
        $defaultExploreUrl = preg_replace('/feeds\.en\.json$/', '', $defaultExploreUrl);

        // Provide both custom and default URLs to frontend
        $this->initialState->provideInitialState('exploreUrl', trim($exploreUrl));
        $this->initialState->provideInitialState('defaultExploreUrl', $defaultExploreUrl);

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
    #[FrontpageRoute(verb: 'GET', url: '/explore/feeds.{lang}.json')]
    public function explore(string $lang)
    {
        $this->userConfig->setValueInt(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId',
            0
        );
        $this->userConfig->setValueInt(
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
