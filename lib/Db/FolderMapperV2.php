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
     *
     * @return Entity[]
     */
    public function findAllFromUser($userId): array
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
                ->from($this->tableName)
                ->where('user_id = :user_id')
                ->where('deleted_at = 0')
                ->setParameter(':user_id', $userId);

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
            ->where('deleted_at = 0');

        return $this->findEntities($builder);
    }

    public function findFromUser(string $userId, int $id): Entity
    {
        $builder = $this->db->getQueryBuilder();
        $builder->select('*')
            ->from($this->tableName)
            ->where('user_id = :user_id')
            ->where('id = :id')
            ->where('deleted_at = 0')
            ->setParameter(':user_id', $userId)
            ->setParameter(':id', $id);

        return $this->findEntity($builder);
    }
}
