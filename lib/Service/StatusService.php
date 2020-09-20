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
        IDBConnection $connection,
        $AppName
    ) {
        $this->settings = $settings;
        $this->appName = $AppName;
        $this->connection = $connection;
    }

    public function isProperlyConfigured()
    {
        $cronMode = $this->settings->getSystemValue('backgroundjobs_mode');
        $cronOff = !$this->settings->getAppValue(
            Application::NAME,
            'useCronUpdates',
            Application::DEFAULT_SETTINGS['useCronUpdates']
        );

        // check for cron modes which may lead to problems
        return $cronMode === 'cron' || $cronOff;
    }


    public function getStatus()
    {
        $version = $this->settings->getAppValue(
            $this->appName,
            'installed_version'
        );

        return [
            'version' => $version,
            'warnings' => [
                'improperlyConfiguredCron' => !$this->isProperlyConfigured(),
                'incorrectDbCharset' => !$this->connection->supports4ByteText()
            ]
        ];
    }
}
