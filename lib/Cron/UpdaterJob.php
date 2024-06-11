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

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;

use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCA\News\Service\UpdaterService;
use OCP\IAppConfig;

class UpdaterJob extends TimedJob
{

    public function __construct(
        ITimeFactory $time,
        private IAppConfig $config,
        private StatusService $statusService,
        private UpdaterService $updaterService
    ) {
        parent::__construct($time);

        $interval = $this->config->getValueString(
            Application::NAME,
            'updateInterval',
            Application::DEFAULT_SETTINGS['updateInterval']
        );

        $this->setInterval($interval);
    }

    /**
     * @return void
     */
    protected function run($argument): void
    {
        $uses_cron = $this->config->getValueBool(
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
