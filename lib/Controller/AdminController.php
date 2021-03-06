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
     * AdminController constructor.
     *
     * @param IRequest    $request     The request
     * @param IConfig     $config      Config for nextcloud
     */
    public function __construct(IRequest $request, IConfig $config)
    {
        parent::__construct(Application::NAME, $request);

        $this->config = $config;
    }

    /**
     * Controller main entry.
     *
     * There are no checks for the index method since the output is
     * rendered in admin/admin.php
     *
     * @return TemplateResponse
     */
    public function index(): TemplateResponse
    {
        return new TemplateResponse($this->appName, 'admin', $this->getData(), 'blank');
    }

    /**
     * Get admin data.
     *
     * @return array
     */
    private function getData(): array
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
    ): array {
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
