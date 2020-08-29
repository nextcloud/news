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
 * Class LegacyFolderMapper
 *
 * @package OCA\News\Db
 * @deprecated use FolderMapper
 */
class FolderMapper extends NewsMapper
{

    const TABLE_NAME = 'news_folders';

    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Folder::class);
    }

    public function find(string $userId, int $id)
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        return $this->findEntity($sql, [$id, $userId]);
    }


    public function findAllFromUser(string $userId): array
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `user_id` = ? ' .
            'AND `deleted_at` = 0';
        $params = [$userId];

        return $this->findEntities($sql, $params);
    }


    public function findByName(string $folderName, string $userId)
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `name` = ? ' .
            'AND `user_id` = ?';
        $params = [$folderName, $userId];

        return $this->findEntities($sql, $params);
    }


    public function delete(Entity $entity): Entity
    {
        parent::delete($entity);

        // someone please slap me for doing this manually :P
        // we needz CASCADE + FKs please
        $sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';
        $params = [$entity->getId()];
        $stmt = $this->execute($sql, $params);
        $stmt->closeCursor();

        $sql = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN ' .
            '(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';

        $stmt = $this->execute($sql);
        $stmt->closeCursor();

        return $entity;
    }


    /**
     * @param int    $deleteOlderThan if given gets all entries with a delete date
     *                                older than that timestamp
     * @param string $userId          if given returns only entries from the given user
     * @return array with the database rows
     */
    public function getToDelete($deleteOlderThan = null, $userId = null)
    {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ';
        $params = [];

        // sometimes we want to delete all entries
        if ($deleteOlderThan !== null) {
            $sql .= 'AND `deleted_at` < ? ';
            $params[] = $deleteOlderThan;
        }

        // we need to sometimes only delete feeds of a user
        if ($userId !== null) {
            $sql .= 'AND `user_id` = ?';
            $params[] = $userId;
        }

        return $this->findEntities($sql, $params);
    }


    /**
     * Deletes all folders of a user
     *
     * @param string $userId the name of the user
     */
    public function deleteUser(string $userId)
    {
        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';
        $this->execute($sql, [$userId]);
    }

    /**
     * NO-OP
     * @return array
     */
    public function findAll(): array
    {
        return [];
    }

    public function findFromUser(string $userId, int $id): Entity
    {
        return $this->find($id, $userId);
    }
}
