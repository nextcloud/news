<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Service;

use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;

/**
 * Class ExportService
 *
 * @package OCA\News\Service
 */
class ExportService
{

    public function __construct(
        private FeedServiceV2 $feedService,
        private ItemServiceV2 $itemService,
    ) {
        /* NO-OP */
    }

    public function articles(string $userId): array
    {
        $feeds = $this->feedService->findAllForUser($userId);
        $starred = $this->itemService->findAllForUser($userId, ['unread' => false, 'starred' => true]);
        $unread = $this->itemService->findAllForUser($userId, ['unread' => true]);

        // Deduplicate items by their ID to avoid exporting duplicates
        $itemsById = [];
        foreach ([$starred, $unread] as $itemList) {
            foreach ($itemList as $item) {
                $itemsById[$item->getId()] = $item;
            }
        }
        $items = array_values($itemsById);

        // build assoc array for fast access
        $feedsDict = [];
        foreach ($feeds as $feed) {
            $feedsDict['feed' . $feed->getId()] = $feed;
        }

        $articles = [];
        foreach ($items as $item) {
            $articles[] = $item->toExport($feedsDict);
        }

        return $articles;
    }
}
