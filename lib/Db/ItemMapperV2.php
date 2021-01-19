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
                ->andWhere('deleted_at = 0')
                ->setParameter('user_id', $userId, IQueryBuilder::PARAM_STR);

        foreach ($params as $key => $value) {
            $builder->andWhere("${key} = :${key}")
                    ->setParameter($key, $value);
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
        $builder->addSelect('*')
            ->from($this->tableName)
            ->andWhere('deleted_at = 0');

        return $this->findEntities($builder);
    }

    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('items.*')
            ->from($this->tableName, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->where('feeds.user_id = :user_id')
            ->andWhere('items.id = :item_id')
            ->andWhere('deleted_at = 0')
            ->setParameter('user_id', $userId, IQueryBuilder::PARAM_STR)
            ->setParameter('item_id', $id, IQueryBuilder::PARAM_STR);

        return $this->findEntity($builder);
    }

    /**
     * Find an item by a GUID hash.
     *
     * @param int    $feedId   ID of the feed
     * @param string $guidHash hash to find with
     *
     * @return Item
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByGuidHash(int $feedId, string $guidHash): Item
    {
        $builder = $this->db->getQueryBuilder();
        $builder->addSelect('*')
            ->from($this->tableName)
            ->andWhere('feed_id = :feed_id')
            ->andWhere('guid_hash = :guid_hash')
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
        $builder->addSelect('*')
            ->from($this->tableName)
            ->andWhere('feed_id = :feed_id')
            ->setParameter('feed_id', $feedId, IQueryBuilder::PARAM_INT);

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
     * @throws \Doctrine\DBAL\Exception|\OCP\DB\Exception
     */
    public function deleteOverThreshold(int $threshold, bool $removeUnread = false): ?int
    {
        $feedQb = $this->db->getQueryBuilder();
        $feedQb->addSelect('feed_id', $feedQb->func()->count('*', 'itemCount'), 'feeds.articles_per_update')
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
            ->andWhere('starred = 0')
            ->orderBy('updated_date', 'DESC');

        if ($removeUnread === false) {
            $rangeQuery->andWhere('unread = 0');
        }

        $total_items = [];
        foreach ($feeds as $feed) {
            if ($feed['itemCount'] < $threshold) {
                continue;
            }

            $rangeQuery->setFirstResult(max($threshold, $feed['articles_per_update']));

            $items = $this->db->executeQuery($rangeQuery->getSQL(), ['feedId' => $feed['feed_id']])
                              ->fetchAll(FetchMode::COLUMN);

            $total_items = array_merge($total_items, $items);
        }

        $deleteQb = $this->db->getQueryBuilder();
        $deleteQb->delete($this->tableName)
                 ->where('id IN (?)');

        return $this->db->executeUpdate($deleteQb->getSQL(), [$total_items], [IQueryBuilder::PARAM_INT_ARRAY]);
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
}
