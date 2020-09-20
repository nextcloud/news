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

use OCA\News\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Controller;

use OCA\News\Service\ItemService;

/**
 * Class AdminController
 *
 * @package OCA\News\Controller
 */
class AdminController extends Controller
{

    /**
     * @var IConfig
     */
    private $config;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * AdminController constructor.
     *
     * @param string      $appName     The name of the app
     * @param IRequest    $request     The request
     * @param IConfig     $config      Config for nextcloud
     * @param ItemService $itemService Service for items
     */
    public function __construct(
        string $appName,
        IRequest $request,
        IConfig $config,
        ItemService $itemService
    ) {
        parent::__construct($appName, $request);
        $this->config      = $config;
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
        return new TemplateResponse($this->appName, 'admin', $this->getData(), 'blank');
    }

    private function getData()
    {
        $data = [];

        foreach (array_keys(Application::DEFAULT_SETTINGS) as $setting) {
            $data[$setting] = $this->config->getAppValue(
                Application::NAME,
                $setting,
                Application::DEFAULT_SETTINGS[$setting]
            );
        }

        return $data;
    }

    /**
     * Update the app config.
     *
     * @param int    $autoPurgeMinimumInterval New minimum interval for auto-purge
     * @param int    $autoPurgeCount           New value of auto-purge count
     * @param int    $maxRedirects             New value for max amount of redirects
     * @param int    $feedFetcherTimeout       New timeout value for feed fetcher
     * @param bool   $useCronUpdates           Whether or not to use cron updates
     * @param string $exploreUrl               URL to use for the explore feed
     * @param int    $updateInterval           Interval in which the feeds will be updated
     *
     * @return array with the updated values
     */
    public function update(
        int $autoPurgeMinimumInterval,
        int $autoPurgeCount,
        int $maxRedirects,
        int $feedFetcherTimeout,
        bool $useCronUpdates,
        string $exploreUrl,
        int $updateInterval
    ) {
        $this->config->setAppValue($this->appName, 'autoPurgeMinimumInterval', $autoPurgeMinimumInterval);
        $this->config->setAppValue($this->appName, 'autoPurgeCount', $autoPurgeCount);
        $this->config->setAppValue($this->appName, 'maxRedirects', $maxRedirects);
        $this->config->setAppValue($this->appName, 'feedFetcherTimeout', $feedFetcherTimeout);
        $this->config->setAppValue($this->appName, 'useCronUpdates', $useCronUpdates);
        $this->config->setAppValue($this->appName, 'exploreUrl', $exploreUrl);
        $this->config->setAppValue($this->appName, 'updateInterval', $updateInterval);

        return $this->getData();
    }
}
