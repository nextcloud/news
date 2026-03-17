<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Paul Tirk <paultirk@paultirk.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2020 Paul Tirk
 */

return ['routes' => [
// admin (no AdminController class yet; kept here for future implementation)
['name' => 'admin#update', 'url' => '/admin', 'verb' => 'PUT'],
['name' => 'admin#migrate', 'url' => '/admin/migrate', 'verb' => 'POST'],

// page manifest (no manifest() method on PageController)
['name' => 'page#manifest', 'url' => '/manifest.webapp', 'verb' => 'GET'],

// wildcard CORS preflight for all API routes (FrontpageRoute does not auto-handle OPTIONS)
['name' => 'utility_api#preflighted_cors', 'url' => '/api/{apiVersion}/{path}', 'verb' => 'OPTIONS', 'requirements' => ['apiVersion' => 'v(1-[23]|2)', 'path' => '.+']],
]];

