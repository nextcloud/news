<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Service;

use Doctrine\DBAL\Platforms\MySqlPlatform;

use OCP\IConfig;
use OCP\IDBConnection;

use OCA\News\Config\Config;


class StatusService {

    private $settings;
    private $config;
    private $appName;
    /**
     * @var IDBConnection
     */
    private $connection;

    public function __construct(IConfig $settings, IDBConnection $connection,
                                Config $config, $AppName) {
        $this->settings = $settings;
        $this->config = $config;
        $this->appName = $AppName;
        $this->connection = $connection;
    }

    public function isProperlyConfigured() {
        $cronMode = $this->settings->getAppValue(
            'core', 'backgroundjobs_mode'
        );
        $cronOff = !$this->config->getUseCronUpdates();

        // check for cron modes which may lead to problems
        return $cronMode === 'cron' || $cronOff;
    }


    public function getStatus() {
        $version = $this->settings->getAppValue(
            $this->appName, 'installed_version'
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
