<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Sean Molenaar
 * @copyright Sean Molenaar <sean@seanmolenaar.eu> 2020
 */

namespace OCA\News\Migration;

use OCA\News\AppInfo\Application;
use OCA\News\Config\LegacyConfig;
use OCP\IConfig;
use OCP\Migration\IRepairStep;
use OCP\Migration\IOutput;

class MigrateConfig implements IRepairStep
{

    /**
     * @var LegacyConfig
     */
    private $config;

    /**
     * @var IConfig
     */
    private $iConfig;

    /**
     * @param LegacyConfig $config
     * @param IConfig      $iConfig
     * @param Application  $application To make sure the class is found below
     */
    public function __construct(LegacyConfig $config, IConfig $iConfig, Application $application)
    {
        $this->config = $config;
        $this->iConfig = $iConfig;
    }

    public function getName()
    {
        return 'Migrate config to nextcloud managed config';
    }

    public function run(IOutput $output)
    {
        $version = $this->iConfig->getAppValue('news', 'installed_version', '0.0.0');
        if (version_compare($version, '15.0.0', '>')) {
            return;
        }

        $app_keys = $this->iConfig->getAppKeys(Application::NAME);
        foreach ($this->config as $key => $value) {
            if (!isset(Application::DEFAULT_SETTINGS[$key])) {
                continue;
            }
            if (in_array($key, $app_keys)) {
                continue;
            }
            $this->iConfig->setAppValue(Application::NAME, $key, $value);
        }
    }
}
