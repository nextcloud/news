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
use OCP\DB\Exception as DBException;
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
            $builder->andWhere("$key = " . $builder->createNamedParameter($value));
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
     * @return Item
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findForUserByGuidHash(string $userId, int $feedId, string $guidHash): Entity
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
     *
     * @return int
     *
     * @throws DBException
     */
    public function readAll(string $userId, int $maxItemId): int
    {
        $idBuilder = $this->db->getQueryBuilder();
        $idBuilder->select('items.id')
            ->from(ItemMapperV2::TABLE_NAME, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('items.id <= :maxItemId')
            ->setParameter('userId', $userId)
            ->setParameter('maxItemId', $maxItemId);

        $idList = array_map(function ($value): int {
            return intval($value['id']);
        }, $this->db->executeQuery($idBuilder->getSQL(), $idBuilder->getParameters())->fetchAll());

        $builder = $this->db->getQueryBuilder();
        $builder->update(self::TABLE_NAME)
            ->set('unread', $builder->createParameter('unread'))
            ->andWhere('id IN (:idList)')
            ->andWhere('unread != :unread')
            ->setParameter('unread', false, IQueryBuilder::PARAM_BOOL)
            ->setParameter('idList', $idList, IQueryBuilder::PARAM_INT_ARRAY);

        return $this->db->executeUpdate($builder->getSQL(), $builder->getParameters(), $builder->getParameterTypes());
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
                ->addOrderBy('items.id', 'DESC')
                ->setMaxResults(1);

        return $this->findEntity($builder);
    }

    /**
     * @param string $userId
     * @param int    $feedId
     * @param float  $updatedSince
     * @param bool   $hideRead
     *
     * @return Item[]
     */
    public function findAllInFeedAfter(
        string $userId,
        int $feedId,
        float $updatedSince,
        bool $hideRead
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('feeds.id = :feedId')
            ->andWhere('feeds.deleted_at = 0')
            ->setParameters([
                'updatedSince' => $updatedSince,
                'feedId' => $feedId,
                'userId'=> $userId,
            ])
            ->addOrderBy('items.id', 'DESC');

        if ($hideRead === true) {
            $builder->andWhere('items.unread = :unread')
                    ->setParameter('unread', true);
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string   $userId
     * @param int|null $folderId
     * @param float    $updatedSince
     * @param bool     $hideRead
     *
     * @return Item[]
     */
    public function findAllInFolderAfter(
        string $userId,
        ?int $folderId,
        float $updatedSince,
        bool $hideRead
    ): array {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->innerJoin('feeds', FolderMapperV2::TABLE_NAME, 'folders', 'feeds.folder_id = folders.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('feeds.deleted_at = 0')
            ->andWhere('folders.id = :folderId')
            ->setParameters(['updatedSince' => $updatedSince, 'folderId' => $folderId, 'userId' => $userId])
            ->addOrderBy('items.id', 'DESC');

        if ($hideRead === true) {
            $builder->andWhere('items.unread = :unread')
                    ->setParameter('unread', true);
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId
     * @param float  $updatedSince
     * @param int    $feedType
     *
     * @return Item[]|Entity[]
     * @throws ServiceValidationException
     */
    public function findAllAfter(string $userId, int $feedType, float $updatedSince): array
    {
        $builder = $this->db->getQueryBuilder();

        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('items.last_modified >= :updatedSince')
            ->andWhere('feeds.deleted_at = 0')
            ->andWhere('feeds.user_id = :userId')
            ->setParameters(['updatedSince' => $updatedSince, 'userId' => $userId])
            ->addOrderBy('items.id', 'DESC');

        switch ($feedType) {
            case ListType::STARRED:
                $builder->andWhere('items.starred = :starred')
                        ->setParameter('starred', true);
                break;
            case ListType::UNREAD:
                $builder->andWhere('items.unread = :unread')
                        ->setParameter('unread', true);
                break;
            case ListType::ALL_ITEMS:
                break;
            default:
                throw new ServiceValidationException('Unexpected Feed type in call');
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId      User identifier
     * @param int    $feedId      Feed identifier
     * @param int    $limit       Max items to retrieve
     * @param int    $offset      First item ID to retrieve
     * @param bool   $hideRead    Hide read items
     * @param bool   $oldestFirst Chronological sort
     * @param array  $search      Search terms
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
            ->andWhere('feeds.deleted_at = 0')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('items.feed_id = :feedId')
            ->setParameter('userId', $userId)
            ->setParameter('feedId', $feedId)
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        $builder = $this->addSearch($builder, $search);

        if ($limit >= 1) {
            $builder->setMaxResults($limit);
        }

        if ($offset !== 0) {
            $builder->andWhere($this->offsetWhere($oldestFirst))
                    ->setParameter('offset', $offset);
        }

        if ($hideRead === true) {
            $builder->andWhere('items.unread = :unread')
                    ->setParameter('unread', true);
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string   $userId      User identifier
     * @param int|null $folderId    Folder identifier (null for root)
     * @param int      $limit       Max items to retrieve
     * @param int      $offset      First item ID to retrieve
     * @param bool     $hideRead    Hide read items
     * @param bool     $oldestFirst Chronological sort
     * @param array    $search      Search terms
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
            ->andWhere('feeds.deleted_at = 0')
            ->andWhere($folderWhere)
            ->setParameter('userId', $userId)
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        $builder = $this->addSearch($builder, $search);

        if ($limit >= 1) {
            $builder->setMaxResults($limit);
        }

        if ($offset !== 0) {
            $builder->andWhere($this->offsetWhere($oldestFirst))
                    ->setParameter('offset', $offset);
        }

        if ($hideRead === true) {
            $builder->andWhere('items.unread = :unread')
                    ->setParameter('unread', true);
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string $userId      User identifier
     * @param int    $type        Type of items to retrieve
     * @param int    $limit       Max items to retrieve
     * @param int    $offset      First item ID to retrieve
     * @param bool   $oldestFirst Chronological sort
     * @param array  $search      Search terms
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
            ->andWhere('feeds.deleted_at = 0')
            ->setParameter('userId', $userId)
            ->addOrderBy('items.id', ($oldestFirst ? 'ASC' : 'DESC'));

        if ($limit >= 1) {
            $builder->setMaxResults($limit);
        }

        if ($offset !== 0) {
            $builder->andWhere($this->offsetWhere($oldestFirst))
                    ->setParameter('offset', $offset);
        }

        $builder = $this->addSearch($builder, $search);

        switch ($type) {
            case ListType::STARRED:
                $builder->andWhere('items.starred = :starred')
                        ->setParameter('starred', true);
                break;
            case ListType::UNREAD:
                $builder->andWhere('items.unread = :unread')
                        ->setParameter('unread', true);
                break;
            case ListType::ALL_ITEMS:
                break;
            default:
                throw new ServiceValidationException('Unexpected Feed type in call');
        }

        return $this->findEntities($builder);
    }

    /**
     * Add search parameters.
     *
     * @param IQueryBuilder $builder
     * @param array         $terms
     *
     * @return IQueryBuilder
     */
    private function addSearch(IQueryBuilder $builder, array $terms): IQueryBuilder
    {
        if ($terms === []) {
            return $builder;
        }

        foreach ($terms as $key => $term) {
            $term = $this->db->escapeLikeParameter($term);
            $builder->andWhere("items.search_index LIKE :term$key")
                ->setParameter("term$key", "%$term%");
        }

        return $builder;
    }

    /**
     * Generate an expression for the offset.
     *
     * @param bool $oldestFirst Sorting direction
     *
     * @return string
     */
    private function offsetWhere(bool $oldestFirst): string
    {
        if ($oldestFirst === true) {
            return 'items.id > :offset';
        }

        return 'items.id < :offset';
    }
}
