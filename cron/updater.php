<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Cron;

use OCA\News\AppInfo\Application;
use OCA\News\Config\Config;
use OCA\News\Service\StatusService;
use OCA\News\Utility\Updater as UpdaterService;

class Updater {

    public static function run() {
        $app = new Application();

        $container = $app->getContainer();

        // make it possible to turn off cron updates if you use an external
        // script to execute updates in parallel
        $useCronUpdates = $container->query(Config::class)->getUseCronUpdates();
        $isProperlyConfigured = $container->query(StatusService::class)->isProperlyConfigured();
        if ($useCronUpdates && $isProperlyConfigured) {
            $container->query(UpdaterService::class)->update();
            $container->query(UpdaterService::class)->beforeUpdate();
            $container->query(UpdaterService::class)->afterUpdate();
        }
    }

}
