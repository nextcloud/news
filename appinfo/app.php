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

use Exception;

require_once __DIR__ . '/../vendor/autoload.php';


// Turn all errors into exceptions to combat shitty library behavior
set_error_handler(function ($code, $message) {
    if ($code === E_ERROR || $code === E_USER_ERROR) {
        throw new Exception($message, $code);
    }
});

(new Application)->registerConfig();
