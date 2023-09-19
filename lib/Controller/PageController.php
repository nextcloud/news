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
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;

use OCA\News\Service\StatusService;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Db\ListType;
use OCP\IUserSession;

/**
 * @IgnoreOpenAPI
 */
class PageController extends Controller
{
    use JSONHttpErrorTrait;

    /**
     * @var IConfig
     */
    private $settings;

    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * @var IURLGenerator
     */
    private $urlGenerator;

    /**
     * @var RecommendedSites
     */
    private $recommendedSites;

    /**
     * @var StatusService
     */
    private $statusService;

    public function __construct(
        IRequest $request,
        IConfig $settings,
        IURLGenerator $urlGenerator,
        IL10N $l10n,
        RecommendedSites $recommendedSites,
        StatusService $statusService,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
        $this->settings = $settings;
        $this->urlGenerator = $urlGenerator;
        $this->l10n = $l10n;
        $this->recommendedSites = $recommendedSites;
        $this->statusService = $statusService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse
    {
        $status = $this->statusService->getStatus();
        $response = new TemplateResponse(
            $this->appName,
            'index',
            [
                'nc_major_version' => \OCP\Util::getVersion()[0],
                'warnings' => $status['warnings'],
                'url_generator' => $this->urlGenerator
            ]
        );

        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*')// chrome breaks on audio elements
            ->allowEvalScript(true)
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
     * @NoAdminRequired
     */
    public function settings(): array
    {
        $settings = [
            'showAll',
            'compact',
            'preventReadOnScroll',
            'oldestFirst',
            'compactExpand'
        ];

        $exploreUrl = $this->settings->getAppValue(
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

        $result = [
            'language' => $this->l10n->getLanguageCode(),
            'exploreUrl' => $exploreUrl
        ];

        foreach ($settings as $setting) {
            $result[$setting] = $this->settings->getUserValue(
                $this->getUserId(),
                $this->appName,
                $setting
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
     * @param bool $compactExpand
     */
    public function updateSettings(
        bool $showAll,
        bool $compact,
        bool $preventReadOnScroll,
        bool $oldestFirst,
        bool $compactExpand
    ): void {
        $settings = [
            'showAll'             => $showAll,
            'compact'             => $compact,
            'preventReadOnScroll' => $preventReadOnScroll,
            'oldestFirst'         => $oldestFirst,
            'compactExpand'       => $compactExpand,
        ];

        foreach ($settings as $setting => $value) {
            $value = $value ? '1' : '0';
            $this->settings->setUserValue(
                $this->getUserId(),
                $this->appName,
                $setting,
                $value
            );
        }
    }

    /**
     * @NoAdminRequired
     *
     * @param string $lang
     *
     * @return Http\JSONResponse|array
     */
    public function explore(string $lang)
    {
        $this->settings->setUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId',
            0
        );
        $this->settings->setUserValue(
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
