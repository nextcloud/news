<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Cron;

use OC\BackgroundJob\TimedJob;

use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCA\News\Service\UpdaterService;
use OCP\IConfig;

class UpdaterJob extends TimedJob
{

    /**
     * @var IConfig
     */
    private $config;
    /**
     * @var StatusService
     */
    private $statusService;
    /**
     * @var UpdaterService
     */
    private $updaterService;

    public function __construct(
        IConfig $config,
        StatusService $status,
        UpdaterService $updaterService
    ) {
        $this->config = $config;
        $this->statusService = $status;
        $this->updaterService = $updaterService;

        $interval = $this->config->getAppValue(
            Application::NAME,
            'updateInterval',
            Application::DEFAULT_SETTINGS['updateInterval']
        );

        parent::setInterval($interval);
    }

    /**
     * @return void
     */
    protected function run($argument)
    {
        $uses_cron = $this->config->getAppValue(
            Application::NAME,
            'useCronUpdates',
            Application::DEFAULT_SETTINGS['useCronUpdates']
        );

        if (!$uses_cron || !$this->statusService->isCronProperlyConfigured()) {
            return;
        }

        $this->updaterService->beforeUpdate();
        $this->updaterService->update();
        $this->updaterService->afterUpdate();
    }
}
