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

namespace OCA\News\Hooks;

use \OCA\News\App\News;


class User {


    public static function deleteUser($params) {
        $userId = $params['uid'];
        
        $app = new News();
        $container = $app->getContainer();

        // order is important!
        $container->query('ItemBusinessLayer')->deleteUser($userId);
        $container->query('FeedBusinessLayer')->deleteUser($userId);
        $container->query('FolderBusinessLayer')->deleteUser($userId);
    }


}