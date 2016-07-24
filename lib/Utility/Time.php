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
        $utimestamp = microtime(true);
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);
        $result = ($timestamp * 1000000) + $milliseconds;
        return intval($result);
    }

}