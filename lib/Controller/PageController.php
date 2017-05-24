<?php
/**
 * Nextcloud - News
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

use OCP\IRequest;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;

use OCA\News\Service\StatusService;
use OCA\News\Config\Config;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Explore\RecommendedSiteNotFoundException;
use OCA\News\Db\FeedType;

class PageController extends Controller {

    private $settings;
    private $l10n;
    private $userId;
    private $urlGenerator;
    private $config;
    private $recommendedSites;
    private $statusService;

    use JSONHttpError;

    public function __construct($AppName,
                                IRequest $request,
                                IConfig $settings,
                                IURLGenerator $urlGenerator,
                                Config $config,
                                IL10N $l10n,
                                RecommendedSites $recommendedSites,
                                StatusService $statusService,
                                $UserId){
        parent::__construct($AppName, $request);
        $this->settings = $settings;
        $this->urlGenerator = $urlGenerator;
        $this->l10n = $l10n;
        $this->userId = $UserId;
        $this->config = $config;
        $this->recommendedSites = $recommendedSites;
        $this->statusService = $statusService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        $status = $this->statusService->getStatus();
        $response = new TemplateResponse($this->appName, 'index', [
            'warnings' => $status['warnings'],
            'url_generator' => $this->urlGenerator
        ]);

        $csp = new ContentSecurityPolicy();
        $csp->addAllowedImageDomain('*')
            ->addAllowedMediaDomain('*')
            ->addAllowedConnectDomain('*')  // chrome breaks on audio elements
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
    public function settings() {
        $settings = [
            'showAll',
            'compact',
            'preventReadOnScroll',
            'oldestFirst',
            'compactExpand'
        ];

        $exploreUrl = $this->config->getExploreUrl();
        if (trim($exploreUrl) === '') {
            // default url should not feature the sites.en.json
            $exploreUrl = $this->urlGenerator->linkToRoute(
                'news.page.explore', ['lang' => 'en']
            );
            $exploreUrl = preg_replace('/feeds\.en\.json$/', '', $exploreUrl);
        }

        $result = [
            'language' => $this->l10n->getLanguageCode(),
            'exploreUrl' => $exploreUrl
        ];

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
    public function updateSettings($showAll, $compact, $preventReadOnScroll,
                                   $oldestFirst, $compactExpand) {
        $settings = ['showAll',
            'compact',
            'preventReadOnScroll',
            'oldestFirst',
            'compactExpand'
        ];

        foreach ($settings as $setting) {
            if (${$setting}) {
                $value = '1';
            } else {
                $value = '0';
            }
            $this->settings->setUserValue($this->userId, $this->appName,
                                          $setting, $value);
        }
    }

    /**
     * @NoAdminRequired
     *
     * @param string $lang
     */
    public function explore($lang) {
        $this->settings->setUserValue($this->userId, $this->appName,
            'lastViewedFeedId', 0);
        $this->settings->setUserValue($this->userId, $this->appName,
            'lastViewedFeedType', FeedType::EXPLORE);

        try {
            return $this->recommendedSites->forLanguage($lang);
        } catch (RecommendedSiteNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }


}
