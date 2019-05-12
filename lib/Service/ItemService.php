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

use OCA\News\Db\Item;
use OCP\IConfig;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\ItemMapper;
use OCA\News\Db\FeedType;
use OCA\News\Config\Config;
use OCA\News\Utility\Time;

class ItemService extends Service
{

    private $config;
    private $timeFactory;
    private $itemMapper;
    private $systemConfig;

    public function __construct(
        ItemMapper $itemMapper,
        Time $timeFactory,
        Config $config,
        IConfig $systemConfig
    ) {
        parent::__construct($itemMapper);
        $this->config = $config;
        $this->timeFactory = $timeFactory;
        $this->itemMapper = $itemMapper;
        $this->systemConfig = $systemConfig;
    }


    /**
     * Returns all new items
     *
     * @param  int     $id           the id of the feed, 0 for starred or all items
     * @param  int     $type         the type of the feed
     * @param  int     $updatedSince a timestamp with the last modification date
     *                               returns only items with a >= modified
     *                               timestamp
     * @param  boolean $showAll      if unread items should also be returned
     * @param  string  $userId       the name of the user
     * @return array of items
     */
    public function findAllNew($id, $type, $updatedSince, $showAll, $userId)
    {
        switch ($type) {
            case FeedType::FEED:
                return $this->itemMapper->findAllNewFeed(
                    $id,
                    $updatedSince,
                    $showAll,
                    $userId
                );
            case FeedType::FOLDER:
                return $this->itemMapper->findAllNewFolder(
                    $id,
                    $updatedSince,
                    $showAll,
                    $userId
                );
            default:
                return $this->itemMapper->findAllNew(
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
     * @param  int      $id          the id of the feed, 0 for starred or all items
     * @param  int      $type        the type of the feed
     * @param  int      $limit       how many items should be returned
     * @param  int      $offset      the offset
     * @param  boolean  $showAll     if unread items should also be returned
     * @param  boolean  $oldestFirst if it should be ordered by oldest first
     * @param  string   $userId      the name of the user
     * @param  string[] $search      an array of keywords that the result should
     *                               contain in either the author, title, link
     *                               or body
     * @return array of items
     */
    public function findAll(
        $id,
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
                return $this->itemMapper->findAllFeed(
                    $id,
                    $limit,
                    $offset,
                    $showAll,
                    $oldestFirst,
                    $userId,
                    $search
                );
            case FeedType::FOLDER:
                return $this->itemMapper->findAllFolder(
                    $id,
                    $limit,
                    $offset,
                    $showAll,
                    $oldestFirst,
                    $userId,
                    $search
                );
            default:
                return $this->itemMapper->findAll(
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


    /**
     * Star or unstar an item
     *
     * @param  int     $feedId    the id of the item's feed that should be starred
     * @param  string  $guidHash  the guidHash of the item that should be starred
     * @param  boolean $isStarred if true the item will be marked as starred,
     *                            if false unstar
     * @param  string  $userId    the name of the user for security reasons
     * @throws ServiceNotFoundException if the item does not exist
     */
    public function star($feedId, $guidHash, $isStarred, $userId)
    {
        try {
            /**
 * @var Item $item
*/
            $item = $this->itemMapper->findByGuidHash(
                $guidHash,
                $feedId,
                $userId
            );

            $item->setStarred($isStarred);

            $this->itemMapper->update($item);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }


    /**
     * Read or unread an item
     *
     * @param  int     $itemId the id of the item that should be read
     * @param  boolean $isRead if true the item will be marked as read,
     *                         if false unread
     * @param  string  $userId the name of the user for security reasons
     * @throws ServiceNotFoundException if the item does not exist
     */
    public function read($itemId, $isRead, $userId)
    {
        try {
            $lastModified = $this->timeFactory->getMicroTime();
            $this->itemMapper->readItem($itemId, $isRead, $lastModified, $userId);
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
     */
    public function readAll($highestItemId, $userId)
    {
        $time = $this->timeFactory->getMicroTime();
        $this->itemMapper->readAll($highestItemId, $time, $userId);
    }


    /**
     * Set a folder read
     *
     * @param int    $folderId      the id of the folder that should be marked read
     * @param int    $highestItemId all items below that are marked read. This is
     *                              used to prevent marking items as read that
     *                              the users hasn't seen yet
     * @param string $userId        the name of the user
     */
    public function readFolder($folderId, $highestItemId, $userId)
    {
        $time = $this->timeFactory->getMicroTime();
        $this->itemMapper->readFolder(
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
     */
    public function readFeed($feedId, $highestItemId, $userId)
    {
        $time = $this->timeFactory->getMicroTime();
        $this->itemMapper->readFeed($feedId, $highestItemId, $time, $userId);
    }


    /**
     * This method deletes all unread feeds that are not starred and over the
     * count of $this->autoPurgeCount starting by the oldest. This is to clean
     * up the database so that old entries don't spam your db. As criteria for
     * old, the id is taken
     */
    public function autoPurgeOld()
    {
        $count = $this->config->getAutoPurgeCount();
        if ($count >= 0) {
            $this->itemMapper->deleteOlderThanThreshold($count);
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
            return $this->itemMapper->getNewestItemId($userId);
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
        return $this->itemMapper->starredCount($userId);
    }


    /**
     * @param string $userId from which user the items should be taken
     * @return array of items which are starred or unread
     */
    public function getUnreadOrStarred($userId)
    {
        return $this->itemMapper->findAllUnreadOrStarred($userId);
    }


    /**
     * Deletes all items of a user
     *
     * @param string $userId the name of the user
     */
    public function deleteUser($userId)
    {
        $this->itemMapper->deleteUser($userId);
    }


    /**
     * Regenerates the search index for all items
     */
    public function generateSearchIndices()
    {
        $this->itemMapper->updateSearchIndices();
    }
}
