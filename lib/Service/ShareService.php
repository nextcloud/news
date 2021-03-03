<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Marco Nassabain <marco.nassabain@hotmail.com>
 */

namespace OCA\News\Service;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;

use \Psr\Log\LoggerInterface;

/**
 * Class ImportService
 *
 * @package OCA\News\Service
 */
class ShareService
{
    /**
     * Items service.
     *
     * @var ItemServiceV2
     */
    protected $itemService;

    /**
     * Feeds service.
     *
     * @var FeedServiceV2
     */
    protected $feedService;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ShareService constructor
     *
     * @param FeedServiceV2   $feedService Service for feeds
     * @param ItemServiceV2   $itemService Service to manage items
     * @param LoggerInterface $logger      Logger
     */
    public function __construct(
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        LoggerInterface $logger
    ) {
        $this->itemService = $itemService;
        $this->feedService = $feedService;
        $this->logger      = $logger;
    }

    /**
     * Share an item with a user
     *
     * @param string $userId      ID of user sharing the item
     * @param int    $id          Item ID
     * @param string $shareWithId ID of user to share with
     *
     * Sharing by copying - the item is duplicated, and the 'sharedBy'
     * field is filled accordingly.
     * The item is then placed in a dummy feed reserved for items
     * shared with the user
     *
     * @return Item
     * @throws ServiceNotFoundException|ServiceConflictException
     */
    public function shareItemWithUser(string $userId, int $id, string $shareRecipientId)
    {
        // find item to share
        try {
            $item = $this->itemService->find($userId, $itemId);
        } catch (DoesNotExistException $ex) {
            throw ServiceNotFoundException::from($ex);
        }

        // duplicate the item
        $sharedItem = clone $item;

        // initialize fields
        $sharedItem->setUnread(true);
        $sharedItem->setStarred(false);
        $sharedItem->setSharedBy($userId);

        // get 'shared with me' dummy feed
        // TODO: move to feedService->createSharedWithMeFeed() ?
        $feedUrl = 'http://nextcloud/sharedwithme';
        $feed = $this->feedService->findByUrl($shareRecipientId, $feedUrl);
        if (is_null($feed)) {
            $feed = new Feed();
            $feed->setUserId($shareRecipientId)
                 ->setUrlHash(md5($feedUrl))
                 ->setLink($feedUrl)
                 ->setUrl($feedUrl)
                 ->setTitle('Shared with me')
                 ->setAdded(time())
                 ->setFolderId(null)
                 ->setPreventUpdate(true);

            $feed = $this->feedService->insert($feed);
        }

        $sharedItem->setFeedId($feed->getId());

        return $this->itemService->insertOrUpdate($sharedItem);
    }
}
