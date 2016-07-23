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

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;

use OCA\News\Config\Config;
use OCA\News\Service\itemService;

class AdminController extends Controller {

    private $config;
    private $configPath;
    private $itemService;

    public function __construct($AppName, IRequest $request, Config $config,
                                ItemService $itemService, $configFile){
        parent::__construct($AppName, $request);
        $this->config = $config;
        $this->configPath = $configFile;
        $this->itemService = $itemService;
    }

    // There are no checks for the index method since the output is rendered
    // in admin/admin.php
    public function index() {
        $data = [
            'autoPurgeMinimumInterval' =>
                $this->config->getAutoPurgeMinimumInterval(),
            'autoPurgeCount' => $this->config->getAutoPurgeCount(),
            'maxRedirects' => $this->config->getMaxRedirects(),
            'feedFetcherTimeout' => $this->config->getFeedFetcherTimeout(),
            'useCronUpdates' => $this->config->getUseCronUpdates(),
            'maxSize' => $this->config->getMaxSize(),
            'exploreUrl' => $this->config->getExploreUrl(),
        ];
        return new TemplateResponse($this->appName, 'admin', $data, 'blank');
    }


    /**
     * @param int $autoPurgeMinimumInterval
     * @param int $autoPurgeCount
     * @param int $maxRedirects
     * @param int $feedFetcherTimeout
     * @param int $maxSize
     * @param bool $useCronUpdates
     * @param string $exploreUrl
     * @return array with the updated values
     */
    public function update($autoPurgeMinimumInterval, $autoPurgeCount,
                           $maxRedirects, $feedFetcherTimeout, $maxSize,
                           $useCronUpdates, $exploreUrl) {
        $this->config->setAutoPurgeMinimumInterval($autoPurgeMinimumInterval);
        $this->config->setAutoPurgeCount($autoPurgeCount);
        $this->config->setMaxRedirects($maxRedirects);
        $this->config->setMaxSize($maxSize);
        $this->config->setFeedFetcherTimeout($feedFetcherTimeout);
        $this->config->setUseCronUpdates($useCronUpdates);
        $this->config->setExploreUrl($exploreUrl);
        $this->config->write($this->configPath);

        return [
            'autoPurgeMinimumInterval' =>
                $this->config->getAutoPurgeMinimumInterval(),
            'autoPurgeCount' => $this->config->getAutoPurgeCount(),
            'maxRedirects' => $this->config->getMaxRedirects(),
            'maxSize' => $this->config->getMaxSize(),
            'feedFetcherTimeout' => $this->config->getFeedFetcherTimeout(),
            'useCronUpdates' => $this->config->getUseCronUpdates(),
            'exploreUrl' => $this->config->getExploreUrl(),
        ];
    }

}
