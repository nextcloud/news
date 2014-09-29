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

use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\IConfig;
use \OCP\IL10N;
use \OCP\IURLGenerator;
use \OCP\AppFramework\Controller;

use \OCA\News\Config\AppConfig;

class PageController extends Controller {

	private $settings;
	private $l10n;
	private $userId;
	private $appConfig;
	private $urlGenerator;

	public function __construct($appName,
	                            IRequest $request,
	                            IConfig $settings,
	                            IURLGenerator $urlGenerator,
                                AppConfig $appConfig,
	                            IL10N $l10n,
	                            $userId){
		parent::__construct($appName, $request);
		$this->settings = $settings;
		$this->urlGenerator = $urlGenerator;
		$this->appConfig = $appConfig;
		$this->l10n = $l10n;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new TemplateResponse($this->appName, 'index');
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


    /**
     * @NoCSRFRequired
     * @PublicPage
     *
     * Generates a web app manifest, according to specs in:
     * https://developer.mozilla.org/en-US/Apps/Build/Manifest
     */
    public function manifest() {
        $config = $this->appConfig->getConfig();

        // size of the icons: 128x128 is required by FxOS for all app manifests
        $iconSizes = ['128', '512'];
        $icons = [];

        $locale = str_replace('_', '-', $this->l10n->getLanguageCode());

        foreach ($iconSizes as $size) {
            $filename = 'app-' . $size . '.png';
            if (file_exists(__DIR__ . '/../img/' . $filename)) {
                $icons[$size] = $this->urlGenerator->imagePath($config['id'],
                    $filename);
            }
        }

        $authors = [];
        foreach ($config['authors'] as $author) {
            $authors[] = $author['name'];
        }

        return [
            "name" => $config['name'],
            "type" => 'web',
            "default_locale" => $locale,
            "description" => $config['description'],
            "launch_path" => $this->urlGenerator->linkToRoute(
                $config['id'] . '.page.index'),
            "icons" => $icons,
            "developer" => [
                "name" => implode(', ', $authors),
                "url" => $config['homepage']
            ]
        ];
    }

}