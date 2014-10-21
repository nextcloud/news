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

/**
 * Register all needed autoloaders
 */

// composer libs
require_once __DIR__ . '/../3rdparty/autoload.php';

// non composer libs
$thirdPartyLibs = [
    '\ZendXML\Security' => 'ZendXml/vendor/autoload.php',
];

foreach ($thirdPartyLibs as $class => $path) {
    if (!class_exists($class)) {
        require_once __DIR__ . '/../3rdparty/' . $path;
    }
}
