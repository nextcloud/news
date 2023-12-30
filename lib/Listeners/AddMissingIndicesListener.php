<?php

/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Benjamin Brahmer <info@b-brahmer.de>
 * @copyright 2023 Benjamin Brahmer
 */

 namespace OCA\News\Listeners;

 use OCP\EventDispatcher\Event;
 use OCP\EventDispatcher\IEventListener;
 use OCP\DB\Events\AddMissingIndicesEvent;

 /**
 * @template-implements IEventListener<Event|AddMissingIndicesEvent>
 */
class AddMissingIndicesListener implements IEventListener
{
    public function handle(Event $event): void
    {
        if (!$event instanceof AddMissingIndicesEvent) {
            return;
        }

        $event->addMissingIndex('news_feeds', 'news_feeds_deleted_at_index', ['deleted_at']);
    }
}
