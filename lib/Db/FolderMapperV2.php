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
use OCP\AppFramework\Db\Entity;
use OCP\DB\Exception as DBException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Class FolderMapper
 *
 * @package OCA\News\Db
 */
class FolderMapperV2 extends NewsMapperV2
{
    const TABLE_NAME = 'news_folders';

    /**
     * FolderMapper constructor.
     *
     * @param IDBConnection $db
     * @param Time          $time
     */
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Folder::class);
    }

    /**
     * Find all feeds for a user.
     *
     * @param string $userId The user identifier
     * @param array  $params Filter parameters
     *
     * @return Folder[]
     */
    public function findAllFromUser(string $userId, array $params = []): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
                ->from($this->tableName)
                ->where('user_id = :user_id')
                ->andWhere('deleted_at = 0')
                ->setParameter('user_id', $userId)
                ->addOrderBy('name');

        return $this->findEntities($builder);
    }

    /**
     * Find all items
     *
     * @return Folder[]
     */
    public function findAll(): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->where('deleted_at = 0')
            ->addOrderBy('name');

        return $this->findEntities($builder);
    }

    /**
     * Find a single feed for a user
     *
     * @param string $userId The user identifier
     * @param int    $id     The feed ID
     *
     * @return Folder
     * @throws \OCP\AppFramework\Db\DoesNotExistException
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
     */
    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->where('user_id = :user_id')
            ->andWhere('id = :id')
            ->andWhere('deleted_at = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('id', $id);

        return $this->findEntity($builder);
    }

    /**
     * @param string   $userId
     * @param int      $id
     * @param int|null $maxItemId
     *
     * @return int
     *
     * @throws DBException
     *
     */
    public function read(string $userId, int $id, ?int $maxItemId = null): int
    {
        $idBuilder = $this->db->getQueryBuilder();
        $idBuilder->select('items.id')
                  ->from(ItemMapperV2::TABLE_NAME, 'items')
                  ->innerJoin('items', FeedMapperV2::TABLE_NAME, 'feeds', 'items.feed_id = feeds.id')
                  ->andWhere('feeds.user_id = :userId')
                  ->andWhere('feeds.folder_id = :folderId')
                  ->setParameter('userId', $userId)
                  ->setParameter('folderId', $id);

        if ($maxItemId !== null) {
            $idBuilder->andWhere('items.id <= :maxItemId')
                      ->setParameter('maxItemId', $maxItemId);
        }

        $idList = array_map(function ($value): int {
            return intval($value['id']);
        }, $this->db->executeQuery($idBuilder->getSQL(), $idBuilder->getParameters())->fetchAll());

        $time = new Time();
        $builder = $this->db->getQueryBuilder();
        $builder->update(ItemMapperV2::TABLE_NAME)
            ->set('unread', $builder->createParameter('unread'))
            ->set('last_modified', $builder->createParameter('last_modified'))
            ->andWhere('id IN (:idList)')
            ->andWhere('unread != :unread')
            ->setParameter('unread', false, IQueryBuilder::PARAM_BOOL)
            ->setParameter('idList', $idList, IQueryBuilder::PARAM_INT_ARRAY)
            ->setParameter('last_modified', $time->getMicroTime(), IQueryBuilder::PARAM_STR);

        return $this->db->executeStatement(
            $builder->getSQL(),
            $builder->getParameters(),
            $builder->getParameterTypes()
        );
    }
}
