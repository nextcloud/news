<?php
/**
 * ownCloud - News
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

use OCP\IConfig;

use OCA\News\Config\Config;


class StatusService {

    private $settings;
    private $config;
    private $appName;

    public function __construct(IConfig $settings, Config $config, $AppName) {
        $this->settings = $settings;
        $this->config = $config;
        $this->appName = $AppName;
    }


    public function getStatus() {
        $improperlyConfiguredCron = false;

        $version = $this->settings->getAppValue(
            $this->appName, 'installed_version'
        );
        $cronMode = $this->settings->getAppValue(
            'core', 'backgroundjobs_mode'
        );
        $cronOn = $this->config->getUseCronUpdates();

        // check for cron modes which may lead to problems
        if ($cronMode !== 'cron' && $cronOn) {
            $improperlyConfiguredCron = true;
        }


        return [
            'version' => $version,
            'warnings' => [
                'improperlyConfiguredCron' => $improperlyConfiguredCron
            ]
        ];
    }

}