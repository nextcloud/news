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
use OCP\IConfig;
use OCP\IDBConnection;

class StatusService
{
    /** @var IConfig */
    private $settings;
    /** @var string */
    private $appName;
    /** @var IDBConnection */
    private $connection;

    public function __construct(
        IConfig $settings,
        IDBConnection $connection
    ) {
        $this->settings = $settings;
        $this->connection = $connection;
        $this->appName = Application::NAME;
    }

    /**
     * Check if cron is properly configured
     *
     * @return bool
     */
    public function isCronProperlyConfigured(): bool
    {
        //Is NC cron enabled?
        $cronMode = $this->settings->getAppValue('core', 'backgroundjobs_mode');
        //Expect nextcloud cron
        $cronOff = !boolval($this->settings->getAppValue(
            Application::NAME,
            'useCronUpdates',
            (string)Application::DEFAULT_SETTINGS['useCronUpdates']
        ));

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
        $version = $this->settings->getAppValue(
            $this->appName,
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
}
