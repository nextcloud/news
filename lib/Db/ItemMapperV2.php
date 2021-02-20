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

namespace OCA\News\Db;

use OC\DB\QueryBuilder\Literal;
use OCA\News\Service\Exceptions\ServiceValidationException;
use Doctrine\DBAL\FetchMode;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Class ItemMapper
 *
 * @package OCA\News\Db
 */
class ItemMapperV2 extends NewsMapperV2
{
    const TABLE_NAME = 'news_items';

    /**
     * ItemMapper constructor.
     *
     * @param IDBConnection $db
     * @param Time          $time
     */
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Item::class);
    }

    /**
     * Find all feeds for a user.
     *
     * @param string $userId The user identifier
     * @param array  $params Filter parameters
     *
     * @return Entity[]
     */
    public function findAllFromUser(string $userId, array $params = []): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('items.*')
                ->from($this->tableName, 'items')
                ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
                ->where('feeds.user_id = :user_id')
                ->andWhere('feeds.deleted_at = 0')
                ->setParameter('user_id', $userId, IQueryBuilder::PARAM_STR);

        foreach ($params as $key => $value) {
            $builder->andWhere("${key} = " . $builder->createNamedParameter($value));
        }

        return $this->findEntities($builder);
    }

    /**
     * Find all items
     *
     * @return Entity[]
     */
    public function findAll(): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.deleted_at = 0');

        return $this->findEntities($builder);
    }

    /**
     * @inheritDoc
     */
    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->where('feeds.user_id = :user_id')
            ->andWhere('items.id = :item_id')
            ->andWhere('feeds.deleted_at = 0')
            ->setParameter('user_id', $userId, IQueryBuilder::PARAM_STR)
            ->setParameter('item_id', $id, IQueryBuilder::PARAM_INT);

        return $this->findEntity($builder);
    }

    /**
     * Find an item by a GUID hash.
     *
     * @param int    $feedId   ID of the feed
     * @param string $guidHash hash to find with
     *
     * @return Item|Entity
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByGuidHash(int $feedId, string $guidHash): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->andWhere('feed_id = :feed_id')
            ->andWhere('guid_hash = :guid_hash')
            ->setParameter('feed_id', $feedId, IQueryBuilder::PARAM_INT)
            ->setParameter('guid_hash', $guidHash, IQueryBuilder::PARAM_STR);

        return $this->findEntity($builder);
    }

    /**
     * Find a user item by a GUID hash.
     *
     * @param string $userId
     * @param int    $feedId   ID of the feed
     * @param string $guidHash hash to find with
     *
     * @return Item|Entity
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findForUserByGuidHash(string $userId, int $feedId, string $guidHash): Item
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :user_id')
            ->andWhere('feeds.id = :feed_id')
            ->andWhere('items.guid_hash = :guid_hash')
            ->setParameter('user_id', $userId, IQueryBuilder::PARAM_STR)
            ->setParameter('feed_id', $feedId, IQueryBuilder::PARAM_INT)
            ->setParameter('guid_hash', $guidHash, IQueryBuilder::PARAM_STR);

        return $this->findEntity($builder);
    }

    /**
     * @param int $feedId
     *
     * @return array
     */
    public function findAllForFeed(int $feedId): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->where('feed_id = :feed_identifier')
            ->setParameter('feed_identifier', $feedId, IQueryBuilder::PARAM_INT);

        return $this->findEntities($builder);
    }

    /**
     * Delete items from feed that are over the max item threshold
     *
     * @param int  $threshold    Deletion threshold
     * @param bool $removeUnread If unread articles should be removed
     *
     * @return int|null Removed items
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteOverThreshold(int $threshold, bool $removeUnread = false): ?int
    {
        $feedQb = $this->db->getQueryBuilder();
        $feedQb->select('feed_id', $feedQb->func()->count('*', 'itemCount'))
               ->selectAlias($feedQb->func()->max('feeds.articles_per_update'), 'articlesPerUpdate')
               ->from($this->tableName, 'items')
               ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
               ->groupBy('feed_id');

        $feeds = $this->db->executeQuery($feedQb->getSQL())
                          ->fetchAll(FetchMode::ASSOCIATIVE);

        if ($feeds === []) {
            return null;
        }

        $rangeQuery = $this->db->getQueryBuilder();
        $rangeQuery->select('id')
            ->from($this->tableName)
            ->where('feed_id = :feedId')
            ->andWhere('starred = false')
            ->orderBy('last_modified', 'DESC')
            ->addOrderBy('id', 'DESC');

        if ($removeUnread === false) {
            $rangeQuery->andWhere('unread = false');
        }

        $total_items = [];
        foreach ($feeds as $feed) {
            if ($feed['itemCount'] < $threshold) {
                continue;
            }

            $rangeQuery->setFirstResult(max($threshold, $feed['articlesPerUpdate']));

            $items = $this->db->executeQuery($rangeQuery->getSQL(), ['feedId' => $feed['feed_id']])
                              ->fetchAll(FetchMode::COLUMN);

            $total_items = array_merge($total_items, $items);
        }

        $deleteQb = $this->db->getQueryBuilder();
        $deleteQb->delete($this->tableName)
                 ->where('id IN (?)');

        $affected_rows = 0;
        // split $total_items into multiple chunks because of the parameter limit
        foreach (array_chunk($total_items, NewsMapperV2::PDO_PARAMS_LIMIT) as $items_chunk) {
            $affected_rows += $this->db->executeUpdate(
                $deleteQb->getSQL(),
                [$items_chunk],
                [IQueryBuilder::PARAM_INT_ARRAY]
            );
        }
        return $affected_rows;
    }

    /**
     * No-op clear deleted items.
     *
     * @param string|null $userID
     * @param int|null    $oldestDelete
     */
    public function purgeDeleted(?string $userID, ?int $oldestDelete): void
    {
        //NO-OP
    }


    /**
     * @param string $userId
     * @param int    $maxItemId
     *
     * @TODO: Update this for NC 21
     */
    public function readAll(string $userId, int $maxItemId): void
    {
        $builder = $this->db->getQueryBuilder();

        $builder->update($this->tableName, 'items')
                ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
                ->setValue('unread', 0)
                ->andWhere('items.id =< :maxItemId')
                ->andWhere('feeds.user_id = :userId')
                ->setParameter('maxItemId', $maxItemId)
                ->setParameter('userId', $userId);

        $this->db->executeUpdate($builder->getSQL());
    }

    /**
     * @param string $userId
     *
     * @return Entity|Item
     *
     * @throws DoesNotExistException            The item is not found
     * @throws MultipleObjectsReturnedException Multiple items found
     */
    public function newest(string $userId): Entity
    {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
                ->from($this->tableName, 'items')
                ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
                ->where('feeds.user_id = :userId')
                ->setParameter('userId', $userId)
                ->orderBy('items.last_modified', 'DESC')
                ->addOrderBy('items.id', 'DESC')
                ->setMaxResults(1);

        return $this->findEntity($builder);
    }

    /**
     * @param string $userId
     * @param int    $feedId
     * @param int    $updatedSince
     * @param bool   $hideRead
     *
     * @return Item[]
     */
    public function findAllInFeedAfter(
        string $userId,
        int $feedId,
        int $updatedSince,
        bool $hideRead
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('feeds.id = :feedId')
            ->setParameters([
                'updatedSince' => $updatedSince,
                'feedId' => $feedId,
                'userId'=> $userId,
            ])
            ->orderBy('items.last_modified', 'DESC')
            ->addOrderBy('items.id', 'DESC');

        if ($hideRead === true) {
            $builder->andWhere('items.unread = 1');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string   $userId
     * @param int|null $folderId
     * @param int      $updatedSince
     * @param bool     $hideRead
     *
     * @return Item[]
     */
    public function findAllInFolderAfter(
        string $userId,
        ?int $folderId,
        int $updatedSince,
        bool $hideRead
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->innerJoin('feeds', FolderMapperV2::TABLE_NAME, 'folders', 'feeds.folder_id = folders.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('folders.id = :folderId')
            ->setParameters(['updatedSince' => $updatedSince, 'folderId' => $folderId, 'userId' => $userId])
            ->orderBy('items.last_modified', 'DESC')
            ->addOrderBy('items.id', 'DESC');

        if ($hideRead === true) {
            $builder->andWhere('items.unread = 1');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId
     * @param int    $updatedSince
     * @param int    $feedType
     *
     * @return Item[]|Entity[]
     * @throws ServiceValidationException
     */
    public function findAllAfter(string $userId, int $feedType, int $updatedSince): array
    {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.user_id = :userId')
            ->setParameters(['updatedSince' => $updatedSince, 'userId' => $userId])
            ->orderBy('items.last_modified', 'DESC')
            ->addOrderBy('items.id', 'DESC');

        switch ($feedType) {
            case ListType::STARRED:
                $builder->andWhere('items.starred = 1');
                break;
            case ListType::UNREAD:
                $builder->andWhere('items.unread = 1');
                break;
            case ListType::ALL_ITEMS:
                break;
            default:
                throw new ServiceValidationException('Unexpected Feed type in call');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId
     * @param int    $feedId
     * @param int    $limit
     * @param int    $offset
     * @param bool   $hideRead
     * @param bool   $oldestFirst
     * @param array  $search
     *
     * @return Item[]
     */
    public function findAllFeed(
        string $userId,
        int $feedId,
        int $limit,
        int $offset,
        bool $hideRead,
        bool $oldestFirst,
        array $search
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('items.feed_id = :feedId')
            ->setParameter('userId', $userId)
            ->setParameter('feedId', $feedId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('items.last_modified', ($oldestFirst ? 'ASC' : 'DESC'))
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        if ($search !== []) {
            foreach ($search as $key => $term) {
                $term = $this->db->escapeLikeParameter($term);
                $builder->andWhere("items.search_index LIKE :term${key}")
                    ->setParameter("term${key}", "%$term%");
            }
        }

        if ($hideRead === true) {
            $builder->andWhere('items.unread = 1');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string   $userId
     * @param int|null $folderId
     * @param int      $limit
     * @param int      $offset
     * @param bool     $hideRead
     * @param bool     $oldestFirst
     * @param array    $search
     *
     * @return Item[]
     */
    public function findAllFolder(
        string $userId,
        ?int $folderId,
        int $limit,
        int $offset,
        bool $hideRead,
        bool $oldestFirst,
        array $search
    ): array {
        $builder = $this->db->getQueryBuilder();

        if ($folderId === null) {
            $folderWhere = $builder->expr()->isNull('feeds.folder_id');
        } else {
            $folderWhere = $builder->expr()->eq('feeds.folder_id', new Literal($folderId), IQueryBuilder::PARAM_INT);
        }

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere($folderWhere)
            ->setParameter('userId', $userId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('items.last_modified', ($oldestFirst ? 'ASC' : 'DESC'))
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        if ($search !== []) {
            foreach ($search as $key => $term) {
                $term = $this->db->escapeLikeParameter($term);
                $builder->andWhere("items.search_index LIKE :term${key}")
                    ->setParameter("term${key}", "%$term%");
            }
        }

        if ($hideRead === true) {
            $builder->andWhere('items.unread = 1');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId
     * @param int    $type
     * @param int    $limit
     * @param int    $offset
     * @param bool   $oldestFirst
     * @param array  $search
     *
     * @return Item[]
     * @throws ServiceValidationException
     */
    public function findAllItems(
        string $userId,
        int $type,
        int $limit,
        int $offset,
        bool $oldestFirst,
        array $search
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :userId')
            ->setParameter('userId', $userId)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('items.last_modified', ($oldestFirst ? 'ASC' : 'DESC'))
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        if ($search !== []) {
            foreach ($search as $key => $term) {
                $term = $this->db->escapeLikeParameter($term);
                $builder->andWhere("items.search_index LIKE :term${key}")
                        ->setParameter("term${key}", "%$term%");
            }
        }

        switch ($type) {
            case ListType::STARRED:
                $builder->andWhere('items.starred = 1');
                break;
            case ListType::UNREAD:
                $builder->andWhere('items.unread = 1');
                break;
            case ListType::ALL_ITEMS:
                break;
            default:
                throw new ServiceValidationException('Unexpected Feed type in call');
        }

        return $this->findEntities($builder);
    }
}
