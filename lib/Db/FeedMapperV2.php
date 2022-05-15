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

namespace OCA\News\Db;

use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception as DBException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\Entity;

/**
 * Class FeedMapper
 *
 * @package OCA\News\Db
 */
class FeedMapperV2 extends NewsMapperV2
{
    const TABLE_NAME = 'news_feeds';

    /**
     * FeedMapper constructor.
     *
     * @param IDBConnection $db
     * @param Time          $time
     */
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Feed::class);
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
        $builder->select('feeds.*', $builder->func()->count('items.id', 'unreadCount'))
            ->from(static::TABLE_NAME, 'feeds')
            ->leftJoin(
                'feeds',
                ItemMapperV2::TABLE_NAME,
                'items',
                'items.feed_id = feeds.id AND items.unread = :unread'
            )
            ->where('feeds.user_id = :user_id')
            ->andWhere('feeds.deleted_at = 0')
            ->groupBy('feeds.id')
            ->setParameter('unread', true, IQueryBuilder::PARAM_BOOL)
            ->setParameter('user_id', $userId);

        return $this->findEntities($builder);
    }

    /**
     * Find all feeds for a user.
     *
     * @param string $userId The user identifier
     * @param int    $id     The feed identifier
     *
     * @return Feed
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from(static::TABLE_NAME)
            ->where('user_id = :user_id')
            ->andWhere('id = :id')
            ->setParameter('user_id', $userId)
            ->setParameter('id', $id);

        return $this->findEntity($builder);
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
            ->from(static::TABLE_NAME)
            ->where('deleted_at = 0');

        return $this->findEntities($builder);
    }

    /**
     * Find feed by URL
     *
     * @param string $userId The user to find in.
     * @param string $url    The URL to find
     *
     * @return Entity
     *
     * @throws DoesNotExistException            If not found
     * @throws MultipleObjectsReturnedException If multiple found
     */
    public function findByURL(string $userId, string $url): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from(static::TABLE_NAME)
            ->where('user_id = :user_id')
            ->andWhere('url = :url')
            ->setParameter('user_id', $userId)
            ->setParameter('url', $url);

        return $this->findEntity($builder);
    }

    /**
     * Find all feeds in a folder
     *
     * @param int|null $id ID of the folder
     *
     * @return Feed[]
     */
    public function findAllFromFolder(?int $id): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from(static::TABLE_NAME);

        if (is_null($id)) {
            $builder->where('folder_id IS NULL');
        } else {
            $builder->where('folder_id = :folder_id')
                ->setParameter('folder_id', $id);
        }

        return $this->findEntities($builder);
    }

    /**
     * @param string   $userId
     * @param int      $id
     * @param int|null $maxItemID
     *
     * @return int
     * @throws DBException
     *
     */
    public function read(string $userId, int $id, ?int $maxItemID = null): int
    {
        $idBuilder = $this->db->getQueryBuilder();
        $idBuilder->select('items.id')
            ->from(ItemMapperV2::TABLE_NAME, 'items')
            ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
            ->andWhere('feeds.user_id = :userId')
            ->andWhere('feeds.id = :feedId')
            ->setParameter('userId', $userId)
            ->setParameter('feedId', $id);

        if ($maxItemID !== null) {
            $idBuilder->andWhere('items.id <= :maxItemId')
                ->setParameter('maxItemId', $maxItemID);
        }

        $idList = array_map(
            function ($value): int {
                return intval($value['id']);
            },
            $this->db->executeQuery($idBuilder->getSQL(), $idBuilder->getParameters())->fetchAll()
        );

        $builder = $this->db->getQueryBuilder();
        $builder->update(ItemMapperV2::TABLE_NAME)
            ->set('unread', $builder->createParameter('unread'))
            ->andWhere('id IN (:idList)')
            ->andWhere('unread != :unread')
            ->setParameter('unread', false, IQueryBuilder::PARAM_BOOL)
            ->setParameter('idList', $idList, IQueryBuilder::PARAM_INT_ARRAY);

        return $this->db->executeStatement(
            $builder->getSQL(),
            $builder->getParameters(),
            $builder->getParameterTypes()
        );
    }
}
