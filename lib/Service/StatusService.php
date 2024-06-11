<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Service;

use OCA\News\AppInfo\Application;
use OCP\IAppConfig;
use OCP\IDBConnection;
use OCP\BackgroundJob\IJobList;
use OCA\News\Cron\UpdaterJob;

class StatusService
{
    public function __construct(
        private IAppConfig $settings,
        private IDBConnection $connection,
        private IJobList $jobList
    ) {
    }

    /**
     * Check if cron is properly configured
     *
     * @return bool
     */
    public function isCronProperlyConfigured(): bool
    {
        //Is NC cron enabled?
        $cronMode = $this->settings->getValueString('core', 'backgroundjobs_mode', '');
        //Expect nextcloud cron
        $cronOff = !$this->settings->getValueBool(
            Application::NAME,
            'useCronUpdates',
            Application::DEFAULT_SETTINGS['useCronUpdates']
        );

        // check for cron modes which may lead to problems
        return $cronMode === 'cron' || $cronOff;
    }


    /**
     * Get the app status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $version = $this->settings->getValueString(
            Application::NAME,
            'installed_version'
        );

        return [
            'version' => $version,
            'warnings' => [
                'improperlyConfiguredCron' => !$this->isCronProperlyConfigured(),
                'incorrectDbCharset' => !$this->connection->supports4ByteText()
            ]
        ];
    }

    /**
     * Get last update time
     */
    public function getUpdateTime(): int
    {

        $time = 0;

        $myJobList = $this->jobList->getJobsIterator(UpdaterJob::class, 1, 0);
        $time = $myJobList->current()->getLastRun();

        return $time;
    }
}
