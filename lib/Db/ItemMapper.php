<?php
/**
 * Nextcloud - News
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

use Exception;
use OCA\News\Utility\Time;
use OCP\IDBConnection;


class ItemMapper extends NewsMapper {

    public function __construct(IDBConnection $db, Time $time) {
        parent::__construct($db, 'news_items', Item::class, $time);
    }

    private function makeSelectQuery($prependTo = '', $oldestFirst = false,
                                     $distinctFingerprint = false) {
        if ($oldestFirst) {
            $ordering = 'ASC';
        } else {
            $ordering = 'DESC';
        }

        return 'SELECT `items`.* FROM `*PREFIX*news_items` `items` ' .
        'JOIN `*PREFIX*news_feeds` `feeds` ' .
        'ON `feeds`.`id` = `items`.`feed_id` ' .
        'AND `feeds`.`deleted_at` = 0 ' .
        'AND `feeds`.`user_id` = ? ' .
        $prependTo .
        'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
        'ON `folders`.`id` = `feeds`.`folder_id` ' .
        'WHERE `feeds`.`folder_id` = 0 ' .
        'OR `folders`.`deleted_at` = 0 ' .
        'ORDER BY `items`.`id` ' . $ordering;
    }

    /**
     * check if type is feed or all items should be shown
     * @param bool $showAll
     * @param int|null $type
     * @return string
     */
    private function buildStatusQueryPart($showAll, $type = null) {
        $sql = '';

        if (isset($type) && $type === FeedType::STARRED) {
            $sql = 'AND `items`.`starred` = true ';
        } elseif (!$showAll) {
            $sql .= 'AND `items`.`unread` = true ';
        }

        return $sql;
    }

    private function buildSearchQueryPart(array $search = []) {
        return str_repeat('AND `items`.`search_index` LIKE ? ', count($search));
    }

    /**
     * wrap and escape search parameters in a like statement
     *
     * @param string[] $search an array of strings that should be searched
     * @return array with like parameters
     */
    private function buildLikeParameters($search = []) {
        return array_map(function ($param) {
            $param = addcslashes($param, '\\_%');
            return '%' . mb_strtolower($param, 'UTF-8') . '%';
        }, $search);
    }

    /**
     * @param int $id
     * @param string $userId
     * @return \OCA\News\Db\Item
     */
    public function find($id, $userId) {
        $sql = $this->makeSelectQuery('AND `items`.`id` = ? ');
        return $this->findEntity($sql, [$userId, $id]);
    }

    public function starredCount($userId) {
        $sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `feeds`.`deleted_at` = 0 ' .
            'AND `feeds`.`user_id` = ? ' .
            'AND `items`.`starred` = true ' .
            'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
            'ON `folders`.`id` = `feeds`.`folder_id` ' .
            'WHERE `feeds`.`folder_id` = 0 ' .
            'OR `folders`.`deleted_at` = 0';

        $params = [$userId];

        $result = $this->execute($sql, $params)->fetch();

        return (int)$result['size'];
    }


    public function readAll($highestItemId, $time, $userId) {
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = false ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` IN (' .
            'SELECT `id` FROM `*PREFIX*news_feeds` ' .
            'WHERE `user_id` = ? ' .
            ') ' .
            'AND `id` <= ?';
        $params = [$time, $userId, $highestItemId];
        $this->execute($sql, $params);
    }


    public function readFolder($folderId, $highestItemId, $time, $userId) {
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = false ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` IN (' .
            'SELECT `id` FROM `*PREFIX*news_feeds` ' .
            'WHERE `folder_id` = ? ' .
            'AND `user_id` = ? ' .
            ') ' .
            'AND `id` <= ?';
        $params = [$time, $folderId, $userId,
            $highestItemId];
        $this->execute($sql, $params);
    }


    public function readFeed($feedId, $highestItemId, $time, $userId) {
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = false ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` = ? ' .
            'AND `id` <= ? ' .
            'AND EXISTS (' .
            'SELECT * FROM `*PREFIX*news_feeds` ' .
            'WHERE `user_id` = ? ' .
            'AND `id` = ? ) ';
        $params = [$time, $feedId, $highestItemId,
            $userId, $feedId];

        $this->execute($sql, $params);
    }


    private function getOperator($oldestFirst) {
        if ($oldestFirst) {
            return '>';
        } else {
            return '<';
        }
    }


    public function findAllNew($updatedSince, $type, $showAll, $userId) {
        $sql = $this->buildStatusQueryPart($showAll, $type);

        $sql .= 'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFolder($id, $updatedSince, $showAll, $userId) {
        $sql = $this->buildStatusQueryPart($showAll);

        $sql .= 'AND `feeds`.`folder_id` = ? ' .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFeed($id, $updatedSince, $showAll, $userId) {
        $sql = $this->buildStatusQueryPart($showAll);

        $sql .= 'AND `items`.`feed_id` = ? ' .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    private function findEntitiesIgnoringNegativeLimit($sql, $params, $limit) {
        // ignore limit if negative to offer a way to return all feeds
        if ($limit >= 0) {
            return $this->findEntities($sql, $params, $limit);
        } else {
            return $this->findEntities($sql, $params);
        }
    }


    public function findAllFeed($id, $limit, $offset, $showAll, $oldestFirst,
                                $userId, $search = []) {
        $params = [$userId];
        $params = array_merge($params, $this->buildLikeParameters($search));
        $params[] = $id;

        $sql = $this->buildStatusQueryPart($showAll);
        $sql .= $this->buildSearchQueryPart($search);

        $sql .= 'AND `items`.`feed_id` = ? ';
        if ($offset !== 0) {
            $sql .= 'AND `items`.`id` ' .
                $this->getOperator($oldestFirst) . ' ? ';
            $params[] = $offset;
        }
        $sql = $this->makeSelectQuery($sql, $oldestFirst, $search);
        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    public function findAllFolder($id, $limit, $offset, $showAll, $oldestFirst,
                                  $userId, $search = []) {
        $params = [$userId];
        $params = array_merge($params, $this->buildLikeParameters($search));
        $params[] = $id;

        $sql = $this->buildStatusQueryPart($showAll);
        $sql .= $this->buildSearchQueryPart($search);

        $sql .= 'AND `feeds`.`folder_id` = ? ';
        if ($offset !== 0) {
            $sql .= 'AND `items`.`id` ' .
                $this->getOperator($oldestFirst) . ' ? ';
            $params[] = $offset;
        }
        $sql = $this->makeSelectQuery($sql, $oldestFirst, $search);
        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    public function findAll($limit, $offset, $type, $showAll, $oldestFirst, $userId,
                            $search = []) {
        $params = [$userId];
        $params = array_merge($params, $this->buildLikeParameters($search));
        $sql = $this->buildStatusQueryPart($showAll, $type);
        $sql .= $this->buildSearchQueryPart($search);

        if ($offset !== 0) {
            $sql .= 'AND `items`.`id` ' .
                $this->getOperator($oldestFirst) . ' ? ';
            $params[] = $offset;
        }

        $sql = $this->makeSelectQuery($sql, $oldestFirst);

        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    public function findAllUnreadOrStarred($userId) {
        $params = [$userId];
        $sql = 'AND `items`.`unread` = true OR `items`.`starred` = true ';
        $sql = $this->makeSelectQuery($sql);
        return $this->findEntities($sql, $params);
    }


    public function findByGuidHash($guidHash, $feedId, $userId) {
        $sql = $this->makeSelectQuery(
            'AND `items`.`guid_hash` = ? ' .
            'AND `feeds`.`id` = ? ');

        return $this->findEntity($sql, [$userId, $guidHash, $feedId]);
    }


    /**
     * Delete all items for feeds that have over $threshold unread and not
     * starred items
     * @param int $threshold the number of items that should be deleted
     */
    public function deleteReadOlderThanThreshold($threshold) {
        $params = [$threshold];

        $sql = 'SELECT (COUNT(*) - `feeds`.`articles_per_update`) AS `size`, ' .
            '`feeds`.`id` AS `feed_id`, `feeds`.`articles_per_update` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `items`.`unread` = false ' .
            'AND `items`.`starred` = false ' .
            'GROUP BY `feeds`.`id`, `feeds`.`articles_per_update` ' .
            'HAVING COUNT(*) > ?';

        $result = $this->execute($sql, $params);

        while ($row = $result->fetch()) {

            $size = (int)$row['size'];
            $limit = $size - $threshold;

            if ($limit > 0) {
                $params = [$row['feed_id'], $limit];

                $sql = 'DELETE FROM `*PREFIX*news_items` ' .
                    'WHERE `id` IN (' .
                    'SELECT `id` FROM `*PREFIX*news_items` ' .
                    'WHERE `items`.`unread` = false ' .
                    'AND `items`.`starred` = false ' .
                    'AND `feed_id` = ? ' .
                    'ORDER BY `id` ASC ' .
                    'LIMIT ?' .
                    ')';

                $this->execute($sql, $params);
            }
        }

    }


    public function getNewestItemId($userId) {
        $sql = 'SELECT MAX(`items`.`id`) AS `max_id` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `feeds`.`user_id` = ?';
        $params = [$userId];

        $result = $this->findOneQuery($sql, $params);

        return (int)$result['max_id'];
    }


    /**
     * Deletes all items of a user
     * @param string $userId the name of the user
     */
    public function deleteUser($userId) {
        $sql = 'DELETE FROM `*PREFIX*news_items` ' .
            'WHERE `feed_id` IN (' .
            'SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds` ' .
            'WHERE `feeds`.`user_id` = ?' .
            ')';

        $this->execute($sql, [$userId]);
    }


    /**
     * Returns a list of ids and userid of all items
     */
    public function findAllIds($limit = null, $offset = null) {
        $sql = 'SELECT `id` FROM `*PREFIX*news_items`';
        return $this->execute($sql, [], $limit, $offset)->fetchAll();
    }

    /**
     * Update search indices of all items
     */
    public function updateSearchIndices() {
        // update indices in steps to prevent memory issues on larger systems
        $step = 1000;  // update 1000 items at a time
        $itemCount = 1;
        $offset = 0;

        // stop condition if there are no previously fetched items
        while ($itemCount > 0) {
            $items = $this->findAllIds($step, $offset);
            $itemCount = count($items);
            $this->updateSearchIndex($items);
            $offset += $step;
        }
    }

    private function updateSearchIndex(array $items = []) {
        foreach ($items as $row) {
            $sql = 'SELECT * FROM `*PREFIX*news_items` WHERE `id` = ?';
            $params = [$row['id']];
            $item = $this->findEntity($sql, $params);
            $item->generateSearchIndex();
            $this->update($item);
        }
    }

    public function readItem($itemId, $isRead, $lastModified, $userId) {
        $item = $this->find($itemId, $userId);

        // reading an item should set all of the same items as read, whereas
        // marking an item as unread should only mark the selected instance
        // as unread
        if ($isRead) {
            $sql = 'UPDATE `*PREFIX*news_items`
                SET `unread` = false,
                    `last_modified` = ?
                WHERE `fingerprint` = ?
                    AND `feed_id` IN (
                        SELECT `f`.`id` FROM `*PREFIX*news_feeds` AS `f`
                            WHERE `f`.`user_id` = ?
                    )';
            $params = [$lastModified, $item->getFingerprint(), $userId];
            $this->execute($sql, $params);
        } else {
            $item->setLastModified($lastModified);
            $item->setUnread(true);
            $this->update($item);
        }
    }

}
