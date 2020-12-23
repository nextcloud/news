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
 * Class LegacyFeedMapper
 *
 * @package OCA\News\Db
 * @deprecated use FeedMapper
 */
class FeedMapper extends NewsMapper
{
    const TABLE_NAME = 'news_feeds';

    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Feed::class);
    }


    public function find(string $userId, int $id)
    {
        $sql = 'SELECT `feeds`.*, `item_numbers`.`unread_count` ' .
            'FROM `*PREFIX*news_feeds` `feeds` ' .
            'JOIN ( ' .
                'SELECT `feeds`.`id`, COUNT(`items`.`id`) AS `unread_count` ' .
                'FROM `*PREFIX*news_feeds` `feeds` ' .
                'LEFT JOIN `*PREFIX*news_items` `items` ' .
                    'ON `feeds`.`id` = `items`.`feed_id` ' .
                    // WARNING: this is a desperate attempt at making this query
                    // work because prepared statements dont work. This is a
                    // POSSIBLE SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
                    // think twice when changing this
                    'AND `items`.`unread` = ? ' .
                'WHERE `feeds`.`id` = ? ' .
                  'AND `feeds`.`user_id` = ? ' .
                'GROUP BY `feeds`.`id` ' .
            ') `item_numbers` ' .
            'ON `item_numbers`.`id` = `feeds`.`id` ';
        $params = [true, $id, $userId];

        return $this->findEntity($sql, $params);
    }


    public function findAllFromUser(string $userId): array
    {
        $sql = 'SELECT `feeds`.*, `item_numbers`.`unread_count` ' .
            'FROM `*PREFIX*news_feeds` `feeds` ' .
            'JOIN ( ' .
                'SELECT `feeds`.`id`, COUNT(`items`.`id`) AS `unread_count` ' .
                'FROM `*PREFIX*news_feeds` `feeds` ' .
                'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
                    'ON `feeds`.`folder_id` = `folders`.`id` ' .
                'LEFT JOIN `*PREFIX*news_items` `items` ' .
                    'ON `feeds`.`id` = `items`.`feed_id` ' .
                    // WARNING: this is a desperate attempt at making this query
                    // work because prepared statements dont work. This is a
                    // POSSIBLE SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
                    // think twice when changing this
                    'AND `items`.`unread` = ? ' .
                'WHERE `feeds`.`user_id` = ? ' .
                  'AND (`feeds`.`folder_id` IS NULL ' .
                   'OR `folders`.`deleted_at` = 0 ' .
                  ') ' .
                  'AND `feeds`.`deleted_at` = 0 ' .
                'GROUP BY `feeds`.`id` ' .
            ') `item_numbers` ' .
            'ON `item_numbers`.`id` = `feeds`.`id` ';
        $params = [true, $userId];

        return $this->findEntities($sql, $params);
    }


    public function findAll(): array
    {
        $sql = 'SELECT `feeds`.*, `item_numbers`.`unread_count` ' .
            'FROM `*PREFIX*news_feeds` `feeds` ' .
            'JOIN ( ' .
                'SELECT `feeds`.`id`, COUNT(`items`.`id`) AS `unread_count` ' .
                'FROM `*PREFIX*news_feeds` `feeds` ' .
                'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
                    'ON `feeds`.`folder_id` = `folders`.`id` ' .
                'LEFT JOIN `*PREFIX*news_items` `items` ' .
                    'ON `feeds`.`id` = `items`.`feed_id` ' .
                    // WARNING: this is a desperate attempt at making this query
                    // work because prepared statements dont work. This is a
                    // POSSIBLE SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
                    // think twice when changing this
                    'AND `items`.`unread` = ? ' .
                'WHERE (`feeds`.`folder_id` IS NULL ' .
                   'OR `folders`.`deleted_at` = 0 ' .
                ') ' .
                'AND `feeds`.`deleted_at` = 0 ' .
                'GROUP BY `feeds`.`id` ' .
            ') `item_numbers` ' .
            'ON `item_numbers`.`id` = `feeds`.`id` ';

        return $this->findEntities($sql, [true]);
    }


    public function findByUrlHash($hash, $userId)
    {
        $sql = 'SELECT `feeds`.*, `item_numbers`.`unread_count` ' .
            'FROM `*PREFIX*news_feeds` `feeds` ' .
            'JOIN ( ' .
                'SELECT `feeds`.`id`, COUNT(`items`.`id`) AS `unread_count` ' .
                'FROM `*PREFIX*news_feeds` `feeds` ' .
                'LEFT JOIN `*PREFIX*news_items` `items` ' .
                    'ON `feeds`.`id` = `items`.`feed_id` ' .
                    // WARNING: this is a desperate attempt at making this query
                    // work because prepared statements dont work. This is a
                    // POSSIBLE SQL INJECTION RISK WHEN MODIFIED WITHOUT THOUGHT.
                    // think twice when changing this
                    'AND `items`.`unread` = ? ' .
                'WHERE `feeds`.`url_hash` = ? ' .
                  'AND `feeds`.`user_id` = ? ' .
                'GROUP BY `feeds`.`id` ' .
            ') `item_numbers` ' .
            'ON `item_numbers`.`id` = `feeds`.`id` ';
        $params = [true, $hash, $userId];

        return $this->findEntity($sql, $params);
    }


    public function delete(Entity $entity): Entity
    {
        // someone please slap me for doing this manually :P
        // we needz CASCADE + FKs please
        $sql = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` = ?';
        $params = [$entity->getId()];
        $this->execute($sql, $params);

        return parent::delete($entity);
    }


    /**
     * @param int    $deleteOlderThan if given gets all entries with a delete date
     *                                older than that timestamp
     * @param string $userId          if given returns only entries from the given user
     * @return array with the database rows
     */
    public function getToDelete($deleteOlderThan = null, $userId = null)
    {
        $sql = 'SELECT * FROM `*PREFIX*news_feeds` ' .
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
     * Deletes all feeds of a user, delete items first since the user_id
     * is not defined in there
     *
     * @param string $userId the name of the user
     */
    public function deleteUser($userId)
    {
        $sql = 'DELETE FROM `*PREFIX*news_feeds` WHERE `user_id` = ?';
        $this->execute($sql, [$userId]);
    }

    public function findFromUser(string $userId, int $id): Entity
    {
        return $this->find($userId, $id);
    }
}
