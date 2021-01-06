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

namespace OCA\News\Service;

use OCA\News\AppInfo\Application;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\Entity;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\ItemMapper;
use OCA\News\Db\FeedType;
use OCA\News\Utility\Time;
use Psr\Log\LoggerInterface;

/**
 * Class LegacyItemService
 *
 * @package OCA\News\Service
 * @deprecated use ItemServiceV2
 */
class ItemService extends Service
{

    /**
     * @var IConfig
     */
    private $config;
    /**
     * @var Time
     */
    private $timeFactory;
    /**
     * @var ItemMapper
     */
    private $oldItemMapper;

    public function __construct(
        ItemMapperV2 $itemMapper,
        ItemMapper $oldItemMapper,
        Time $timeFactory,
        IConfig $config,
        LoggerInterface $logger
    ) {
        parent::__construct($itemMapper, $logger);
        $this->config = $config;
        $this->timeFactory = $timeFactory;
        $this->oldItemMapper = $oldItemMapper;
    }


    /**
     * Returns all new items
     *
     * @param int|null $id           the id of the feed, 0 for starred or all items
     * @param int      $type         the type of the feed
     * @param int      $updatedSince a timestamp with the last modification date
     *                               returns only items with a >= modified
     *                               timestamp
     * @param boolean  $showAll      if unread items should also be returned
     * @param string   $userId       the name of the user
     *
     * @return array of items
     */
    public function findAllNew(?int $id, $type, $updatedSince, $showAll, $userId)
    {
        switch ($type) {
            case FeedType::FEED:
                return $this->oldItemMapper->findAllNewFeed(
                    $id,
                    $updatedSince,
                    $showAll,
                    $userId
                );
            case FeedType::FOLDER:
                return $this->oldItemMapper->findAllNewFolder(
                    $id,
                    $updatedSince,
                    $showAll,
                    $userId
                );
            default:
                return $this->oldItemMapper->findAllNew(
                    $updatedSince,
                    $type,
                    $showAll,
                    $userId
                );
        }
    }


    /**
     * Returns all items
     *
     * @param int|null $id           the id of the feed, 0 for starred or all items
     * @param int      $type         the type of the feed
     * @param int      $limit        how many items should be returned
     * @param int      $offset       the offset
     * @param boolean  $showAll      if unread items should also be returned
     * @param boolean  $oldestFirst  if it should be ordered by oldest first
     * @param string   $userId       the name of the user
     * @param string[] $search       an array of keywords that the result should
     *                               contain in either the author, title, link
     *                               or body
     *
     * @return array of items
     */
    public function findAllItems(
        ?int $id,
        $type,
        $limit,
        $offset,
        $showAll,
        $oldestFirst,
        $userId,
        $search = []
    ) {
        switch ($type) {
            case FeedType::FEED:
                return $this->oldItemMapper->findAllFeed(
                    $id,
                    $limit,
                    $offset,
                    $showAll,
                    $oldestFirst,
                    $userId,
                    $search
                );
            case FeedType::FOLDER:
                return $this->oldItemMapper->findAllFolder(
                    $id,
                    $limit,
                    $offset,
                    $showAll,
                    $oldestFirst,
                    $userId,
                    $search
                );
            default:
                return $this->oldItemMapper->findAllItems(
                    $limit,
                    $offset,
                    $type,
                    $showAll,
                    $oldestFirst,
                    $userId,
                    $search
                );
        }
    }

    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }


    /**
     * Star or unstar an item
     *
     * @param int     $feedId    the id of the item's feed that should be starred
     * @param string  $guidHash  the guidHash of the item that should be starred
     * @param boolean $isStarred if true the item will be marked as starred,
     *                            if false unstar
     * @param string  $userId    the name of the user for security reasons
     *
     * @throws ServiceNotFoundException if the item does not exist
     *
     * @return void
     */
    public function star($feedId, $guidHash, $isStarred, $userId): void
    {
        try {
            $item = $this->mapper->findByGuidHash($feedId, $guidHash);

            $item->setStarred($isStarred);

            $this->mapper->update($item);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }


    /**
     * Read or unread an item
     *
     * @param int     $itemId the id of the item that should be read
     * @param boolean $isRead if true the item will be marked as read,
     *                         if false unread
     * @param string  $userId the name of the user for security reasons
     *
     * @throws ServiceNotFoundException if the item does not exist
     *
     * @return void
     */
    public function read($itemId, $isRead, $userId): void
    {
        try {
            $lastModified = $this->timeFactory->getMicroTime();
            $this->oldItemMapper->readItem($itemId, $isRead, $lastModified, $userId);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }


    /**
     * Set all items read
     *
     * @param int    $highestItemId all items below that are marked read. This is
     *                              used to prevent marking items as read that
     *                              the users hasn't seen yet
     * @param string $userId        the name of the user
     *
     * @return void
     */
    public function readAll($highestItemId, $userId): void
    {
        $time = $this->timeFactory->getMicroTime();
        $this->oldItemMapper->readAll($highestItemId, $time, $userId);
    }


    /**
     * Set a folder read
     *
     * @param int|null $folderId      the id of the folder that should be marked read
     * @param int      $highestItemId all items below that are marked read. This is
     *                                used to prevent marking items as read that
     *                                the users hasn't seen yet
     * @param string   $userId        the name of the user
     *
     * @return void
     */
    public function readFolder(?int $folderId, $highestItemId, $userId): void
    {
        $time = $this->timeFactory->getMicroTime();
        $this->oldItemMapper->readFolder(
            $folderId,
            $highestItemId,
            $time,
            $userId
        );
    }


    /**
     * Set a feed read
     *
     * @param int    $feedId        the id of the feed that should be marked read
     * @param int    $highestItemId all items below that are marked read. This is
     *                              used to prevent marking items as read that
     *                              the users hasn't seen yet
     * @param string $userId        the name of the user
     *
     * @return void
     */
    public function readFeed($feedId, $highestItemId, $userId): void
    {
        $time = $this->timeFactory->getMicroTime();
        $this->oldItemMapper->readFeed($feedId, $highestItemId, $time, $userId);
    }


    /**
     * This method deletes all unread feeds that are not starred and over the
     * count of $this->autoPurgeCount starting by the oldest. This is to clean
     * up the database so that old entries don't spam your db. As criteria for
     * old, the id is taken
     *
     * @return void
     */
    public function autoPurgeOld(): void
    {
        $count = $this->config->getAppValue(
            Application::NAME,
            'autoPurgeCount',
            Application::DEFAULT_SETTINGS['autoPurgeCount']
        );
        if ($count >= 0) {
            $this->oldItemMapper->deleteReadOlderThanThreshold($count);
        }
    }


    /**
     * Returns the newest item id, use this for marking feeds read
     *
     * @param  string $userId the name of the user
     * @throws ServiceNotFoundException if there is no newest item
     * @return int
     */
    public function getNewestItemId($userId)
    {
        try {
            return $this->oldItemMapper->getNewestItemId($userId);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }


    /**
     * Returns the starred count
     *
     * @param  string $userId the name of the user
     * @return int the count
     */
    public function starredCount($userId)
    {
        return $this->oldItemMapper->starredCount($userId);
    }


    /**
     * @param string $userId from which user the items should be taken
     * @return array of items which are starred or unread
     */
    public function getUnreadOrStarred($userId)
    {
        return $this->oldItemMapper->findAllUnreadOrStarred($userId);
    }


    /**
     * Regenerates the search index for all items
     *
     * @return void
     */
    public function generateSearchIndices(): void
    {
        $this->oldItemMapper->updateSearchIndices();
    }

    public function findAll(): array
    {
        return $this->mapper->findAll();
    }
}
