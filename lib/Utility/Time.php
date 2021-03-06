<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2016 Bernhard Posselt
 */

namespace OCA\News\Utility;

class Time
{
    public function getTime(): int
    {
        return time();
    }

    /**
     * @return string the current unix time in miliseconds
     */
    public function getMicroTime(): string
    {
        list($millisecs, $secs) = explode(" ", microtime());
        return $secs . substr($millisecs, 2, 6);
    }
}
