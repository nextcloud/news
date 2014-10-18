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


namespace OCA\News\Cron;

use \OCA\News\AppInfo\Application;


class Updater {


	static public function run() {
		$app = new Application();

		$container = $app->getContainer();

		// make it possible to turn off cron updates if you use an external
		// script to execute updates in parallel
		if ($container->query('Config')->getUseCronUpdates()) {
			$container->query('Updater')->beforeUpdate();
			$container->query('Updater')->update();
			$container->query('Updater')->afterUpdate();
		}
	}


}
