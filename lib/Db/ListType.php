<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Db;

/**
 * Enum FeedType
 *
 * @package OCA\News\Db
 */
class ListType
{
    const FEED      = 0;
    const FOLDER    = 1;
    const STARRED   = 2;
    const ALL_ITEMS = 3;
    const SHARED    = 4;
    const EXPLORE   = 5;
    const UNREAD    = 6;
}
