<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2020 Sean Molenaar
 */

namespace OCA\News\Service;

use OCA\News\AppInfo\Application;
use OCA\News\Db\Feed;
use OCA\News\Db\ListType;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Class ItemService
 *
 * @package OCA\News\Service
 */
class ItemServiceV2 extends Service
{

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * ItemService constructor.
     *
     * @param ItemMapperV2    $mapper
     * @param IConfig         $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ItemMapperV2 $mapper,
        IConfig $config,
        LoggerInterface $logger
    ) {
        parent::__construct($mapper, $logger);
        $this->config = $config;
    }

    /**
     * Finds all items of a user
     *
     * @param string $userId The ID/name of the user
     * @param array  $params Filter parameters
     *
     *
     * @return Item[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }

    /**
     * Find all items
     *
     * @return Item[]
     */
    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    /**
     * Insert an item or update.
     *
     * @param Item $item
     *
     * @return Entity|Item The updated/inserted item
     */
    public function insertOrUpdate(Item $item): Entity
    {
        try {
            /** @var Item $db_item */
            $db_item = $this->findByGuidHash($item->getFeedId(), $item->getGuidHash());

            // Transfer user modifications
            $item->setUnread($db_item->isUnread())
                 ->setStarred($db_item->isStarred())
                 ->setId($db_item->getId());

            $item->generateSearchIndex();//generates fingerprint

            // We don't want to update the database record if there is no
            // change in the fetched item
            if ($db_item->getFingerprint() === $item->getFingerprint()) {
                $item->resetUpdatedFields();
            }

            return $this->mapper->update($item);
        } catch (DoesNotExistException $exception) {
            return $this->mapper->insert($item);
        }
    }

    /**
     * Return all starred items
     *
     * @param string $userId
     *
     * @return Item[]
     */
    public function starred(string $userId): array
    {
        return $this->findAllForUser($userId, ['starred' => 1]);
    }

    /**
     * Mark an item as read
     *
     * @param string $userId Item owner
     * @param int    $id     Item ID
     * @param bool   $read
     *
     * @return Item
     * @throws ServiceNotFoundException
     * @throws ServiceConflictException
     */
    public function read(string $userId, int $id, bool $read): Entity
    {
        /** @var Item $item */
        $item = $this->find($userId, $id);

        $item->setUnread(!$read);

        return $this->mapper->update($item);
    }

    /**
     * @param int|null $threshold
     * @param bool     $removeUnread
     *
     * @return int|null Amount of deleted items or null if not applicable
     */
    public function purgeOverThreshold(int $threshold = null, bool $removeUnread = false): ?int
    {
        $threshold = (int) ($threshold ?? $this->config->getAppValue(
            Application::NAME,
            'autoPurgeCount',
            Application::DEFAULT_SETTINGS['autoPurgeCount']
        ));

        if ($threshold <= 0) {
            return null;
        }

        return $this->mapper->deleteOverThreshold($threshold, $removeUnread);
    }
    /**
     * Mark an item as starred
     *
     * @param string $userId Item owner
     * @param int    $id     Item ID
     * @param bool   $starred
     *
     * @return Item
     * @throws ServiceNotFoundException|ServiceConflictException
     */
    public function star(string $userId, int $id, bool $starred): Entity
    {
        /** @var Item $item */
        $item = $this->find($userId, $id);

        $item->setStarred($starred);

        return $this->mapper->update($item);
    }

    /**
     * Mark an item as starred by GUID hash
     *
     * @param string $userId Item owner
     * @param int    $feedId Item ID
     * @param string $guidHash
     * @param bool   $starred
     *
     * @return Item
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function starByGuid(string $userId, int $feedId, string $guidHash, bool $starred): Entity
    {
        try {
            $item = $this->mapper->findForUserByGuidHash($userId, $feedId, $guidHash);
        } catch (DoesNotExistException $ex) {
            throw ServiceNotFoundException::from($ex);
        } catch (MultipleObjectsReturnedException $ex) {
            throw ServiceConflictException::from($ex);
        }

        $item->setStarred($starred);

        return $this->mapper->update($item);
    }

    /**
     * Mark all items as read
     *
     * @param string $userId Item owner
     * @param int    $maxItemId
     *
     * @return int
     */
    public function readAll(string $userId, int $maxItemId): int
    {
        return $this->mapper->readAll($userId, $maxItemId);
    }

    /**
     * @param string $userId
     *
     * @return Item
     */
    public function newest(string $userId): Entity
    {
        try {
            return $this->mapper->newest($userId);
        } catch (DoesNotExistException $e) {
            throw ServiceNotFoundException::from($e);
        } catch (MultipleObjectsReturnedException $e) {
            throw ServiceConflictException::from($e);
        }
    }

    /**
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return Item
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByGuidHash(int $feedId, string $guidHash): Entity
    {
        return $this->mapper->findByGuidHash($feedId, $guidHash);
    }

    /**
     * Convenience method to find all items in a feed.
     *
     * @param string $userId
     * @param int    $feedId
     *
     * @return array
     */
    public function findAllInFeed(string $userId, int $feedId): array
    {
        return $this->findAllInFeedAfter($userId, $feedId, PHP_INT_MIN, false);
    }

    /**
     * Returns all new items in a feed
     * @param string  $userId       the name of the user
     * @param int     $feedId       the id of the feed
     * @param float   $updatedSince a timestamp with the minimal modification date
     * @param boolean $hideRead     if unread items should also be returned
     *
     * @return array of items
     */
    public function findAllInFeedAfter(string $userId, int $feedId, float $updatedSince, bool $hideRead): array
    {
        return $this->mapper->findAllInFeedAfter($userId, $feedId, $updatedSince, $hideRead);
    }

    /**
     * Returns all new items in a folder
     * @param string   $userId       the name of the user
     * @param int|null $folderId     the id of the folder
     * @param float    $updatedSince a timestamp with the minimal modification date
     * @param boolean  $hideRead     if unread items should also be returned
     *
     * @return array of items
     */
    public function findAllInFolderAfter(string $userId, ?int $folderId, float $updatedSince, bool $hideRead): array
    {
        return $this->mapper->findAllInFolderAfter($userId, $folderId, $updatedSince, $hideRead);
    }

    /**
     * Returns all new items of a type
     *
     * @param string $userId       the name of the user
     * @param int    $feedType     the type of feed items to fetch. (starred || unread)
     * @param float  $updatedSince a timestamp with the minimal modification date
     *
     * @return array of items
     *
     * @throws ServiceValidationException
     */
    public function findAllAfter(string $userId, int $feedType, float $updatedSince): array
    {
        if (!in_array($feedType, [ListType::STARRED, ListType::UNREAD, ListType::ALL_ITEMS], true)) {
            throw new ServiceValidationException('Trying to find in unknown type');
        }

        return $this->mapper->findAllAfter($userId, $feedType, $updatedSince);
    }


    /**
     * Returns all items
     *
     * @param int $feedId            the id of the feed
     * @param int      $limit        how many items should be returned
     * @param int      $offset       the offset
     * @param boolean  $hideRead      if unread items should also be returned
     * @param boolean  $oldestFirst  if it should be ordered by oldest first
     * @param string   $userId       the name of the user
     * @param string[] $search       an array of keywords that the result should
     *                               contain in either the author, title, link
     *                               or body
     *
     * @return array of items
     */
    public function findAllInFeedWithFilters(
        string $userId,
        int $feedId,
        int $limit,
        int $offset,
        bool $hideRead,
        bool $oldestFirst,
        array $search = []
    ): array {
        return $this->mapper->findAllFeed($userId, $feedId, $limit, $offset, $hideRead, $oldestFirst, $search);
    }
    /**
     * Returns all items
     *
     * @param int|null $folderId     the id of the folder
     * @param int      $limit        how many items should be returned
     * @param int      $offset       the offset
     * @param boolean  $hideRead      if unread items should also be returned
     * @param boolean  $oldestFirst  if it should be ordered by oldest first
     * @param string   $userId       the name of the user
     * @param string[] $search       an array of keywords that the result should
     *                               contain in either the author, title, link
     *                               or body
     *
     * @return array of items
     */
    public function findAllInFolderWithFilters(
        string $userId,
        ?int $folderId,
        int $limit,
        int $offset,
        bool $hideRead,
        bool $oldestFirst,
        array $search = []
    ): array {
        return $this->mapper->findAllFolder($userId, $folderId, $limit, $offset, $hideRead, $oldestFirst, $search);
    }
    /**
     * Returns all items
     *
     * @param int      $type         the type of the feed
     * @param int      $limit        how many items should be returned
     * @param int      $offset       the offset
     * @param boolean  $oldestFirst  if it should be ordered by oldest first
     * @param string   $userId       the name of the user
     * @param string[] $search       an array of keywords that the result should
     *                               contain in either the author, title, link
     *                               or body
     *
     * @return array of items
     */
    public function findAllWithFilters(
        string $userId,
        int $type,
        int $limit,
        int $offset,
        bool $oldestFirst,
        array $search = []
    ): array {
        return $this->mapper->findAllItems($userId, $type, $limit, $offset, $oldestFirst, $search);
    }
}
