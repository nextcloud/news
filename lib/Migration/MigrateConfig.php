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
     * Array of defaults
     *
     * @var array
     */
    private $defaults;

    /**
     * @param LegacyConfig $config
     * @param IConfig      $iConfig
     */
    public function __construct(LegacyConfig $config, IConfig $iConfig)
    {
        $this->config = $config;
        $this->iConfig = $iConfig;

        // copied from Application::default_settings
        $this->defaults = [
            'autoPurgeMinimumInterval' => 60,
            'autoPurgeCount'           => 200,
            'maxRedirects'             => 10,
            'feedFetcherTimeout'       => 60,
            'useCronUpdates'           => true,
            'exploreUrl'               => '',
            'updateInterval'           => 3600,
        ];
    }

    public function getName()
    {
        return 'Migrate config to nextcloud managed config';
    }

    public function run(IOutput $output)
    {
        $version = $this->iConfig->getAppValue('news', 'installed_version', '0.0.0');
        if (version_compare($version, '15.0.6', '>')) {
            return;
        }

        $app_keys = $this->iConfig->getAppKeys('news');
        foreach ($this->config as $key => $value) {
            if (!isset($this->defaults[$key])) {
                continue;
            }
            if (in_array($key, $app_keys)) {
                continue;
            }
            $this->iConfig->setAppValue('news', $key, $value);
        }
    }
}
