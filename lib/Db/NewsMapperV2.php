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
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Db\Entity;

/**
 * Class NewsMapper
 *
 * @package OCA\News\Db
 */
abstract class NewsMapperV2 extends QBMapper
{
    const TABLE_NAME = '';

    /**
     * @var Time
     */
    private $time;

    /**
     * NewsMapper constructor.
     *
     * @param IDBConnection $db     Database connection
     * @param Time          $time   Time class
     * @param string        $entity Entity class
     */
    public function __construct(
        IDBConnection $db,
        Time $time,
        string $entity
    ) {
        parent::__construct($db, static::TABLE_NAME, $entity);
        $this->time = $time;
    }

    public function update(Entity $entity): Entity
    {
        $entity->setLastModified($this->time->getMicroTime());
        return parent::update($entity);
    }

    public function insert(Entity $entity): Entity
    {
        $entity->setLastModified($this->time->getMicroTime());
        return parent::insert($entity);
    }

    /**
     * Remove deleted entities.
     *
     * @param string|null $userID       The user to purge
     * @param int|null    $oldestDelete The timestamp to purge from
     *
     * @return void
     */
    public function purgeDeleted(?string $userID, ?int $oldestDelete): void
    {
        $builder = $this->db->getQueryBuilder();
        $builder->delete($this->tableName)
                ->andWhere('deleted_at != 0');

        if ($userID !== null) {
            $builder->andWhere('user_id = :user_id')
                ->setParameter(':user_id', $userID);
        }

        if ($oldestDelete !== null) {
            $builder->andWhere('deleted_at < :deleted_at')
                    ->setParameter(':deleted_at', $oldestDelete);
        }

        $builder->execute();
    }

    /**
     * Find all items.
     *
     * @return Entity[]
     */
    abstract public function findAll(): array;

    /**
     * Find all items for a user.
     *
     * @param string $userId ID of the user
     * @param array  $params Filter parameters
     *
     * @return Entity[]
     */
    abstract public function findAllFromUser(string $userId, array $params = []): array;

    /**
     * Find item for a user.
     *
     * @param string $userId ID of the user
     * @param int    $id     ID of the item
     *
     * @return Feed
     *
     * @throws DoesNotExistException            The item is not found
     * @throws MultipleObjectsReturnedException Multiple items found
     */
    abstract public function findFromUser(string $userId, int $id): Entity;
}
