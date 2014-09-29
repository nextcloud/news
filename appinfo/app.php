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

namespace OCA\News\AppInfo;

use \OCA\News\Config\DependencyException;


// Turn all errors into exceptions to combat shitty library behavior
set_error_handler(function ($code, $message) {
	if ($code === E_ERROR || $code === E_USER_ERROR) {
		throw new \Exception($message, $code);
	}
});

$container = new Application();

$config = $container->getAppConfig();
$config->registerNavigation();
$config->registerBackgroundJobs();
$config->registerHooks();

// check for correct app dependencies and fail if possible
$config->testDependencies();
