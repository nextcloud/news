<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Eryk J. <infiniti@inventati.org>
 * @copyright 2026 Eryk J.
 */

namespace OCA\News\Db;

use OCA\News\Utility\Time;
use OCP\AppFramework\Db\Entity;
use OCP\DB\Exception as DBException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Class FilterMapperV2
 *
 * @package OCA\News\Db
 */
class FilterMapperV2 extends NewsMapperV2
{
    const TABLE_NAME = 'news_filters';

    /**
     * FilterMapper constructor.
     *
     * @param IDBConnection $db
     * @param Time          $time
     */
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Filter::class);
    }

    /**
     * Find all filters for a user.
     *
     * @param string $userId The user identifier
     *
     * @return Filter[]
     */
    public function findAllFromUser(string $userId, array $params = []): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('f.*')
                ->from($this->tableName, 'f')
                ->innerJoin('f', FeedMapperV2::TABLE_NAME, 'feeds', 'f.feed_id = feeds.id')
                ->where('feeds.user_id = :user_id')
                ->andWhere('feeds.deleted_at = 0')
                ->setParameter('user_id', $userId);

        return $this->findEntities($builder);
    }

    /**
     * Find all filters.
     *
     * @return Filter[]
     */
    public function findAll(): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName);

        return $this->findEntities($builder);
    }

    /**
     * Find a single filter for a user.
     *
     * @param string $userId The user identifier
     * @param int    $id     The filter ID
     *
     * @return Filter
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('f.*')
            ->from($this->tableName, 'f')
            ->innerJoin('f', FeedMapperV2::TABLE_NAME, 'feeds', 'f.feed_id = feeds.id')
            ->where('feeds.user_id = :user_id')
            ->andWhere('f.id = :id')
            ->andWhere('feeds.deleted_at = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('id', $id);

        return $this->findEntity($builder);
    }

    /**
     * Find a filter by feed ID for a user.
     *
     * @param string $userId The user identifier
     * @param int    $feedId The feed ID
     *
     * @return Filter
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function findByFeedId(string $userId, int $feedId): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('f.*')
            ->from($this->tableName, 'f')
            ->innerJoin('f', FeedMapperV2::TABLE_NAME, 'feeds', 'f.feed_id = feeds.id')
            ->where('feeds.user_id = :user_id')
            ->andWhere('f.feed_id = :feed_id')
            ->andWhere('feeds.deleted_at = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('feed_id', $feedId);

        return $this->findEntity($builder);
    }
}