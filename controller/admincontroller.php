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
use \OCP\AppFramework\Controller;

use \OCA\News\Config\Config;

class AdminController extends Controller {

    private $config;
    private $configPath;

    public function __construct($appName, IRequest $request, Config $config,
                                $configPath){
        parent::__construct($appName, $request);
        $this->config = $config;
        $this->configPath = $configPath;
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
     * @return array with the updated values
     */
    public function update($autoPurgeMinimumInterval, $autoPurgeCount,
                           $maxRedirects, $feedFetcherTimeout, $maxSize,
                           $useCronUpdates) {
        $this->config->setAutoPurgeMinimumInterval($autoPurgeMinimumInterval);
        $this->config->setAutoPurgeCount($autoPurgeCount);
        $this->config->setMaxRedirects($maxRedirects);
        $this->config->setMaxSize($maxSize);
        $this->config->setFeedFetcherTimeout($feedFetcherTimeout);
        $this->config->setUseCronUpdates($useCronUpdates);
        $this->config->write($this->configPath);

        return [
            'autoPurgeMinimumInterval' =>
                $this->config->getAutoPurgeMinimumInterval(),
            'autoPurgeCount' => $this->config->getAutoPurgeCount(),
            'maxRedirects' => $this->config->getMaxRedirects(),
            'maxSize' => $this->config->getMaxSize(),
            'feedFetcherTimeout' => $this->config->getFeedFetcherTimeout(),
            'useCronUpdates' => $this->config->getUseCronUpdates(),
        ];
    }


}