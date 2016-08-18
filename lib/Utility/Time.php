<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

namespace OCA\News\Utility;

class Time {
    public function getTime() {
        return time();
    }

    /**
     * @return int the current unix time in miliseconds
     */
    public function getMicroTime() {
        list($millisecs, $secs) = explode(" ", microtime());
        return $secs . substr($millisecs, 2, 6);
    }

}
