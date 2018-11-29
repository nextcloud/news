<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 *
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;

use OCA\News\Config\Config;
use OCA\News\Service\ItemService;

/**
 * Class AdminController
 *
 * @package OCA\News\Controller
 */
class AdminController extends Controller
{
    private $config;
    private $configPath;
    private $itemService;

    /**
     * AdminController constructor.
     *
     * @param string      $appName     The name of the app
     * @param IRequest    $request     The request
     * @param Config      $config      Config for nextcloud
     * @param ItemService $itemService Service for items
     * @param string      $configFile  Path to the config
     */
    public function __construct(
        $appName,
        IRequest $request,
        Config $config,
        ItemService $itemService,
        $configFile
    ) {
        parent::__construct($appName, $request);
        $this->config      = $config;
        $this->configPath  = $configFile;
        $this->itemService = $itemService;
    }

    /**
     * Controller main entry.
     *
     * There are no checks for the index method since the output is
     * rendered in admin/admin.php
     *
     * @return TemplateResponse
     */
    public function index()
    {
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
     * Update the app config.
     *
     * @param int    $autoPurgeMinimumInterval New minimum interval for auto-purge
     * @param int    $autoPurgeCount           New value of auto-purge count
     * @param int    $maxRedirects             New value for max amount of redirects
     * @param int    $feedFetcherTimeout       New timeout value for feed fetcher
     * @param int    $maxSize                  New max feed size
     * @param bool   $useCronUpdates           Whether or not to use cron updates
     * @param string $exploreUrl               URL to use for the explore feed
     *
     * @return array with the updated values
     */
    public function update(
        $autoPurgeMinimumInterval,
        $autoPurgeCount,
        $maxRedirects,
        $feedFetcherTimeout,
        $maxSize,
        $useCronUpdates,
        $exploreUrl
    ) {
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
