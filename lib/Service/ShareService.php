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
use OCP\IURLGenerator;
use OCP\IUserManager;
use \OCP\IL10N;

use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Class ShareService
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
     * @var IURLGenerator
     */
    private $urlGenerator;

    /**
     * @var IUserManager
     */
    private $userManager;

    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * ShareService constructor
     *
     * @param FeedServiceV2   $feedService  Service for feeds
     * @param ItemServiceV2   $itemService  Service to manage items
     * @param IURLGenerator   $urlGenerator URL Generator
     * @param IUserManager    $userManager  User Manager
     * @param IL10N           $l10n         Localization interface
     * @param LoggerInterface $logger       Logger
     */
    public function __construct(
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        IURLGenerator $urlGenerator,
        IUserManager $userManager,
        IL10N $l10n,
        LoggerInterface $logger
    ) {
        $this->itemService  = $itemService;
        $this->feedService  = $feedService;
        $this->urlGenerator = $urlGenerator;
        $this->userManager  = $userManager;
        $this->l10n         = $l10n;
        $this->logger       = $logger;
    }

    /**
     * Share an item with a user
     *
     * @param string $userId           ID of user sharing the item
     * @param int    $itemId           Item ID
     * @param string $shareRecipientId ID of user to share with
     *
     * Sharing by copying - the item is duplicated, and the 'sharedBy'
     * field is filled accordingly.
     * The item is then placed in a dummy feed reserved for items
     * shared with the user
     *
     * @return Item Shared item
     * @throws ServiceNotFoundException|ServiceConflictException
     */
    public function shareItemWithUser(string $userId, int $itemId, string $shareRecipientId)
    {
        // Find item to share
        $item = $this->itemService->find($userId, $itemId);

        // Duplicate item & initialize fields
        $sharedItem = clone $item;
        $sharedItem->setUnread(true);
        $sharedItem->setStarred(false);
        $sharedItem->setSharedBy($userId);

        // TODO: should we set the pub date to the date of share?

        // Get 'shared with me' dummy feed
        $feedUrl = $this->urlGenerator->getBaseUrl() . '/news/sharedwithme';
        $feed = $this->feedService->findByUrl($shareRecipientId, $feedUrl);
        if (is_null($feed)) {
            $feed = new Feed();
            $feed->setUserId($shareRecipientId)
                 ->setUrlHash(md5($feedUrl))
                 ->setLink($feedUrl)
                 ->setUrl($feedUrl)
                 ->setTitle($this->l10n->t('Shared with me'))
                 ->setAdded(time())
                 ->setFolderId(null)
                 ->setPreventUpdate(true);

            $feed = $this->feedService->insert($feed);
        }

        $sharedItem->setFeedId($feed->getId());

        return $this->itemService->insertOrUpdate($sharedItem);
    }

    /**
     * Map sharers display names to shared items.
     *
     * Loops through an array of news items. For all shared items, the function
     * fetches the sharers display name and adds it into the item.
     *
     * @param Item[]  Array containing items that are shared or not
     * @return Item[]
     */
    public function mapSharedByDisplayNames(array $items): array
    {
        foreach ($items as $item) {
            $sharedBy = $item->getSharedBy();
            if (!is_null($sharedBy)) {
                $user = $this->userManager->get($sharedBy);
                if (!is_null($user)) {
                    $item->setSharedByDisplayName($user->getDisplayName());
                }
            }
        }

        return $items;
    }
}
