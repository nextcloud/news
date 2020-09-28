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

namespace OCA\News\Hooks;

use OCA\News\AppInfo\Application;
use OCA\News\Service\ItemService;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\BeforeUserDeletedEvent;

class UserDeleteHook implements IEventListener
{

    /**
     * Handle user deletion
     *
     * @param BeforeUserDeletedEvent $event
     */
    public function handle(Event $event): void
    {
        $userId = $event->getUser()->getUID();

        $app = new Application();
        $container = $app->getContainer();

        // order is important!
        $container->get(ItemService::class)->deleteUser($userId);
        $container->get(FeedService::class)->deleteUser($userId);
        $container->get(FolderService::class)->deleteUser($userId);
    }
}
