<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\AppInfo;

use OCA\News\Hooks\User;

require_once __DIR__ . '/../vendor/autoload.php';

\OCP\Util::connectHook('OC_User', 'pre_deleteUser', User::class, 'deleteUser');