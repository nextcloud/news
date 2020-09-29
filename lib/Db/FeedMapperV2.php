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
     *
     * @return Entity[]
     */
    public function findAllFromUser(string $userId, array $params = []): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->addSelect('*')
                ->from($this->tableName)
                ->where('user_id = :user_id')
                ->andWhere('deleted_at = 0')
                ->setParameter(':user_id', $userId);

        return $this->findEntities($builder);
    }

    /**
     * Find all feeds for a user.
     *
     * @param string $userId The user identifier
     * @param int $id     The feed identifier
     *
     * @return Entity
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->addSelect('*')
                ->from($this->tableName)
                ->where('user_id = :user_id')
                ->where('id = :id')
                ->setParameter(':user_id', $userId)
                ->setParameter(':id', $id);

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
                ->from($this->tableName)
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
        $builder->addSelect('*')
                ->from($this->tableName)
                ->where('user_id = :user_id')
                ->andWhere('url = :url')
                ->setParameter(':user_id', $userId)
                ->setParameter(':url', $url);

        return $this->findEntity($builder);
    }

    /**
     * Find all feeds in a folder
     *
     * @param int $id ID of the folder
     *
     * @return Feed[]
     */
    public function findAllFromFolder(int $id): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->addSelect('*')
                ->from($this->tableName)
                ->where('folder_id = :folder_id')
                ->setParameter(':folder_id', $id);

        return $this->findEntities($builder);
    }
}
