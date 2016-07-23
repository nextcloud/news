<?php
/**
 * Nextcloud - News
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

use OCA\News\AppInfo\Application;
use OCA\News\Service\ItemService;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;

class User {

    public static function deleteUser($params) {
        $userId = $params['uid'];

        $app = new Application();
        $container = $app->getContainer();

        // order is important!
        $container->query(ItemService::class)->deleteUser($userId);
        $container->query(FeedService::class)->deleteUser($userId);
        $container->query(FolderService::class)->deleteUser($userId);
    }

}
