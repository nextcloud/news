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

use OCP\IConfig;
use OCP\IDBConnection;

use OCA\News\Config\Config;

class StatusService
{
    /** @var IConfig */
    private $settings;
    /** @var Config */
    private $config;
    /** @var string */
    private $appName;
    /** @var IDBConnection */
    private $connection;

    public function __construct(
        IConfig $settings,
        IDBConnection $connection,
        Config $config,
        $AppName
    ) {
        $this->settings = $settings;
        $this->config = $config;
        $this->appName = $AppName;
        $this->connection = $connection;
    }

    public function isProperlyConfigured()
    {
        $cronMode = $this->settings->getAppValue(
            'core',
            'backgroundjobs_mode'
        );
        $cronOff = !$this->config->getUseCronUpdates();

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
