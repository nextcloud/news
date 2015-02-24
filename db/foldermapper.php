<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Db;

use \OCP\IDb;
use \OCP\AppFramework\Db\Entity;

class FolderMapper extends NewsMapper {

    public function __construct(IDb $db) {
        parent::__construct($db, 'news_folders', '\OCA\News\Db\Folder');
    }

    public function find($id, $userId){
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `id` = ? ' .
            'AND `user_id` = ?';

        return $this->findEntity($sql, [$id, $userId]);
    }


    public function findAllFromUser($userId){
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `user_id` = ? ' .
            'AND `deleted_at` = 0';
        $params = [$userId];

        return $this->findEntities($sql, $params);
    }


    public function findByName($folderName, $userId){
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `name` = ? ' .
            'AND `user_id` = ?';
        $params = [$folderName, $userId];

        return $this->findEntities($sql, $params);
    }


    public function delete(Entity $entity){
        parent::delete($entity);

        // someone please slap me for doing this manually :P
        // we needz CASCADE + FKs please
        $sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';
        $params = [$entity->getId()];
        $stmt = $this->execute($sql, $params);
        $stmt->closeCursor();

        $sql = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN '.
            '(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';

        $stmt = $this->execute($sql);
        $stmt->closeCursor();
    }


    /**
     * @param int $deleteOlderThan if given gets all entries with a delete date
     * older than that timestamp
     * @param string $userId if given returns only entries from the given user
     * @return array with the database rows
     */
    public function getToDelete($deleteOlderThan=null, $userId=null) {
        $sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
            'WHERE `deleted_at` > 0 ';
        $params = [];

        // sometimes we want to delete all entries
        if ($deleteOlderThan !== null) {
            $sql .= 'AND `deleted_at` < ? ';
            $params[] = $deleteOlderThan;
        }

        // we need to sometimes only delete feeds of a user
        if($userId !== null) {
            $sql .= 'AND `user_id` = ?';
            $params[] = $userId;
        }

        return $this->findEntities($sql, $params);
    }


    /**
     * Deletes all folders of a user
     * @param string $userId the name of the user
     */
    public function deleteUser($userId) {
        $sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';
        $this->execute($sql, [$userId]);
    }


}