<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Cron;

use OC\BackgroundJob\Job;

use OCA\News\Config\Config;
use OCA\News\Service\StatusService;
use OCA\News\Utility\Updater as UpdaterService;

class Updater extends Job {

    /**
     * @var Config
     */
    private $config;
    /**
     * @var StatusService
     */
    private $status;
    /**
     * @var UpdaterService
     */
    private $updaterService;

    public function __construct(Config $config, StatusService $status,
                                UpdaterService $updaterService) {
        $this->config = $config;
        $this->status = $status;
        $this->updaterService = $updaterService;
    }

    protected function run($argument) {
        if ($this->config->getUseCronUpdates() &&
            $this->status->isProperlyConfigured()) {
            $this->updaterService->beforeUpdate();
            $this->updaterService->update();
            $this->updaterService->afterUpdate();
        }
    }

}
