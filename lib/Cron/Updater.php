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

use OCA\News\Config\Config;
use OCA\News\Service\StatusService;
use OCA\News\Utility\Updater as UpdaterService;

class Updater extends TimedJob
{

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

    public function __construct(
        Config $config,
        StatusService $status,
        UpdaterService $updaterService
    ) {
        $this->config = $config;
        $this->status = $status;
        $this->updaterService = $updaterService;

        parent::setInterval($this->config->getUpdateInterval());
    }

    protected function run($argument)
    {
        if ($this->config->getUseCronUpdates()
            && $this->status->isProperlyConfigured()
        ) {
            $this->updaterService->beforeUpdate();
            $this->updaterService->update();
            $this->updaterService->afterUpdate();
        }
    }
}
