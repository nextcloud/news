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

use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\BeforeUserDeletedEvent;

class UserDeleteHook implements IEventListener
{

    public function __construct(
        private ItemServiceV2 $itemService,
        private FeedServiceV2 $feedService,
        private FolderServiceV2 $folderService,
    ) {
    }

    /**
     * Handle user deletion
     *
     * @param BeforeUserDeletedEvent $event
     */
    public function handle(Event $event): void
    {
        if (!($event instanceof BeforeUserDeletedEvent)) {
            return;
        }

        $userId = $event->getUser()->getUID();

        // order is important!
        $this->itemService->deleteUser($userId);
        $this->feedService->deleteUser($userId);
        $this->folderService->deleteUser($userId);
    }
}
