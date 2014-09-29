<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2014
 * @copyright Bernhard Posselt 2014
 */

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\IURLGenerator;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Config\AppConfig;

class AppController extends Controller {

    private $urlGenerator;
    private $appConfig;

    public function __construct($appName,
                                IRequest $request,
                                IURLGenerator $urlGenerator,
                                AppConfig $appConfig){
        parent::__construct($appName, $request);
        $this->urlGenerator = $urlGenerator;
        $this->appConfig = $appConfig;
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