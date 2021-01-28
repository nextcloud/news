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

use Exception;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Class LegacyItemMapper
 *
 * @package OCA\News\Db
 * @deprecated use ItemMapper
 */
class ItemMapper extends NewsMapper
{

    const TABLE_NAME = 'news_items';
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, $time, Item::class);
    }

    private function makeSelectQuery(
        $prependTo = '',
        $oldestFirst = false,
        $distinctFingerprint = false
    ) {
        if ($oldestFirst) {
            $ordering = 'ASC';
        } else {
            $ordering = 'DESC';
        }

        return 'SELECT `items`.* FROM `*PREFIX*news_items` `items` ' .
        'JOIN `*PREFIX*news_feeds` `feeds` ' .
        'ON `feeds`.`id` = `items`.`feed_id` ' .
        'AND `feeds`.`deleted_at` = 0 ' .
        'AND ('.
            '(`feeds`.`user_id` = ? AND `items`.`shared_by` LIKE \'\')'.
            ' XOR `items`.`shared_with` = ?' .
        ') ' .
        $prependTo .
        'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
        'ON `folders`.`id` = `feeds`.`folder_id` ' .
        'WHERE `feeds`.`folder_id` IS NULL ' .
        'OR `folders`.`deleted_at` = 0 ' .
        'ORDER BY `items`.`id` ' . $ordering;
    }

    /**
     * check if type is feed or all items should be shown
     *
     * @param  bool     $showAll
     * @param  int|null $type
     * @return string
     */
    private function buildStatusQueryPart($showAll, $type = null)
    {
        $sql = '';

        if (isset($type) && $type === FeedType::STARRED) {
            $sql = 'AND `items`.`starred` = ';
            $sql .= $this->db->quote(true, IQueryBuilder::PARAM_BOOL) . ' ';
        } elseif (!$showAll || $type === FeedType::UNREAD) {
            $sql .= 'AND `items`.`unread` = ';
            $sql .= $this->db->quote(true, IQueryBuilder::PARAM_BOOL) . ' ';
        }

        return $sql;
    }

    private function buildSearchQueryPart(array $search = [])
    {
        return str_repeat('AND `items`.`search_index` LIKE ? ', count($search));
    }

    /**
     * wrap and escape search parameters in a like statement
     *
     * @param  string[] $search an array of strings that should be searched
     * @return array with like parameters
     */
    private function buildLikeParameters($search = [])
    {
        return array_map(
            function ($param) {
                $param = addcslashes($param, '\\_%');
                return '%' . mb_strtolower($param, 'UTF-8') . '%';
            },
            $search
        );
    }

    /**
     * @param int    $id
     * @param string $userId
     * @return \OCA\News\Db\Item
     */
    public function find(string $userId, int $id)
    {
        $sql = $this->makeSelectQuery('AND `items`.`id` = ? ');
        return $this->findEntity($sql, [$userId, $userId, $id]);
    }

    public function starredCount(string $userId)
    {
        $sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `feeds`.`deleted_at` = 0 ' .
            'AND ('.
                '(`feeds`.`user_id` = ? AND `items`.`shared_by` LIKE \'\')'.
                ' XOR `items`.`shared_with` = ?' .
            ') ' .
            'AND `items`.`starred` = ? ' .
            'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
            'ON `folders`.`id` = `feeds`.`folder_id` ' .
            'WHERE `feeds`.`folder_id` IS NULL ' .
            'OR `folders`.`deleted_at` = 0';

        $params = [$userId, $userId, true];

        $result = $this->execute($sql, $params)->fetch();

        return (int)$result['size'];
    }


    public function readAll(int $highestItemId, $time, string $userId)
    {
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = ? ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` IN (' .
            'SELECT `id` FROM `*PREFIX*news_feeds` ' .
            'WHERE `user_id` = ? ' .
            ') ' .
            'AND `id` <= ?';
        $params = [false, $time, $userId, $highestItemId];
        $this->execute($sql, $params);
    }


    public function readFolder(?int $folderId, $highestItemId, $time, $userId)
    {
        $folderWhere = is_null($folderId) ? 'IS' : '=';
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = ? ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` IN (' .
            'SELECT `id` FROM `*PREFIX*news_feeds` ' .
            "WHERE `folder_id` ${folderWhere} ? " .
            'AND `user_id` = ? ' .
            ') ' .
            'AND `id` <= ?';
        $params = [false, $time, $folderId, $userId,
            $highestItemId];
        $this->execute($sql, $params);
    }


    public function readFeed($feedId, $highestItemId, $time, $userId)
    {
        $sql = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = ? ' .
            ', `last_modified` = ? ' .
            'WHERE `feed_id` = ? ' .
            'AND `id` <= ? ' .
            'AND EXISTS (' .
            'SELECT * FROM `*PREFIX*news_feeds` ' .
            'WHERE `user_id` = ? ' .
            'AND `id` = ? ) ';
        $params = [false, $time, $feedId, $highestItemId,
            $userId, $feedId];

        $this->execute($sql, $params);
    }


    private function getOperator($oldestFirst)
    {
        if ($oldestFirst) {
            return '>';
        } else {
            return '<';
        }
    }


    public function findAllNew($updatedSince, $type, $showAll, $userId)
    {
        $sql = $this->buildStatusQueryPart($showAll, $type);

        $sql .= 'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $userId, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFolder(?int $id, $updatedSince, $showAll, $userId)
    {
        $sql = $this->buildStatusQueryPart($showAll);

        $folderWhere = is_null($id) ? 'IS' : '=';
        $sql .= "AND `feeds`.`folder_id` ${$folderWhere} ? " .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFeed($id, $updatedSince, $showAll, $userId)
    {
        $sql = $this->buildStatusQueryPart($showAll);

        $sql .= 'AND `items`.`feed_id` = ? ' .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    private function findEntitiesIgnoringNegativeLimit($sql, $params, $limit): array
    {
        // ignore limit if negative to offer a way to return all feeds
        if ($limit >= 0) {
            return $this->findEntities($sql, $params, $limit);
        } else {
            return $this->findEntities($sql, $params);
        }
    }


    public function findAllFeed(
        $id,
        $limit,
        $offset,
        $showAll,
        $oldestFirst,
        $userId,
        $search = []
    ) {
        $params = [$userId, $userId];
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


    public function findAllFolder(
        ?int $id,
        $limit,
        $offset,
        $showAll,
        $oldestFirst,
        $userId,
        $search = []
    ) {
        $params = [$userId, $userId];
        $params = array_merge($params, $this->buildLikeParameters($search));
        $params[] = $id;

        $sql = $this->buildStatusQueryPart($showAll);
        $sql .= $this->buildSearchQueryPart($search);

        $folderWhere = is_null($id) ? 'IS' : '=';
        $sql .= "AND `feeds`.`folder_id` ${folderWhere} ? ";
        if ($offset !== 0) {
            $sql .= 'AND `items`.`id` ' . $this->getOperator($oldestFirst) . ' ? ';
            $params[] = $offset;
        }
        $sql = $this->makeSelectQuery($sql, $oldestFirst, $search);
        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    public function findAllItems(
        $limit,
        $offset,
        $type,
        $showAll,
        $oldestFirst,
        $userId,
        $search = []
    ): array {
        $params = [$userId, $userId];
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


    public function findAllUnreadOrStarred($userId)
    {
        $params = [$userId, $userId, true, true];
        $sql = 'AND (`items`.`unread` = ? OR `items`.`starred` = ?) ';
        $sql = $this->makeSelectQuery($sql);
        return $this->findEntities($sql, $params);
    }


    public function findByGuidHash($guidHash, $feedId, $userId)
    {
        $sql = $this->makeSelectQuery(
            'AND `items`.`guid_hash` = ? ' .
            'AND `feeds`.`id` = ? '
        );

        return $this->findEntity($sql, [$userId, $userId, $guidHash, $feedId]);
    }


    /**
     * Delete all items for feeds that have over $threshold unread and not
     * starred items
     *
     * @param int $threshold the number of items that should be deleted
     */
    public function deleteReadOlderThanThreshold($threshold)
    {
        $params = [false, false, $threshold];

        $sql = 'SELECT (COUNT(*) - `feeds`.`articles_per_update`) AS `size`, ' .
            '`feeds`.`id` AS `feed_id`, `feeds`.`articles_per_update` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `items`.`unread` = ? ' .
            'AND `items`.`starred` = ? ' .
            'GROUP BY `feeds`.`id`, `feeds`.`articles_per_update` ' .
            'HAVING COUNT(*) > ?';

        $result = $this->execute($sql, $params);

        while ($row = $result->fetch()) {
            $size = (int)$row['size'];
            $limit = $size - $threshold;
            $feed_id = $row['feed_id'];

            if ($limit > 0) {
                $params = [false, false, $feed_id, $limit];
                $sql = 'SELECT `id` FROM `*PREFIX*news_items` ' .
                    'WHERE `unread` = ? ' .
                    'AND `starred` = ? ' .
                    'AND `feed_id` = ? ' .
                    'ORDER BY `id` ASC ' .
                    'LIMIT 1 ' .
                    'OFFSET ? ';
            }
            $limit_result = $this->execute($sql, $params);
            if ($limit_row = $limit_result->fetch()) {
                $limit_id = (int)$limit_row['id'];
                $params = [false, false, $feed_id, $limit_id];
                $sql = 'DELETE FROM `*PREFIX*news_items` ' .
                    'WHERE `unread` = ? ' .
                    'AND `starred` = ? ' .
                    'AND `feed_id` = ? ' .
                    'AND `id` < ? ';
                $this->execute($sql, $params);
            }
        }
    }


    public function getNewestItemId($userId)
    {
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
     *
     * @param string $userId the name of the user
     */
    public function deleteUser($userId)
    {
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
    public function findAllIds($limit = null, $offset = null)
    {
        $sql = 'SELECT `id` FROM `*PREFIX*news_items`';
        return $this->execute($sql, [], $limit, $offset)->fetchAll();
    }

    /**
     * Update search indices of all items
     */
    public function updateSearchIndices()
    {
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

    private function updateSearchIndex(array $items = [])
    {
        foreach ($items as $row) {
            $sql = 'SELECT * FROM `*PREFIX*news_items` WHERE `id` = ?';
            $params = [$row['id']];
            $item = $this->findEntity($sql, $params);
            $item->generateSearchIndex();
            $this->update($item);
        }
    }

    public function readItem($itemId, $isRead, $lastModified, $userId)
    {
        $item = $this->find($userId, $itemId);

        // reading an item should set all of the same items as read, whereas
        // marking an item as unread should only mark the selected instance
        // as unread
        if ($isRead) {
            $sql = 'UPDATE `*PREFIX*news_items`
                SET `unread` = ?,
                    `last_modified` = ?
                WHERE `fingerprint` = ?
                    AND `feed_id` IN (
                        SELECT `f`.`id` FROM `*PREFIX*news_feeds` AS `f`
                        WHERE ('.
                            '(`f`.`user_id` = ? AND `shared_by` LIKE \'\')'.
                            ' XOR `shared_with` = ?' .
                        ')' .
                    ')';
            $params = [false, $lastModified, $item->getFingerprint(), $userId, $userId];
            $this->execute($sql, $params);
        } else {
            $item->setLastModified($lastModified);
            $item->setUnread(true);
            $this->update($item);
        }
    }

    /**
     * NO-OP
     *
     * @param string $userId
     *
     * @return array
     */
    public function findAllFromUser(string $userId): array
    {
        return [];
    }

    public function findFromUser(string $userId, int $id): Entity
    {
        return $this->find($id, $userId);
    }

    /**
     * NO-OP
     * @return array
     */
    public function findAll(): array
    {
        return [];
    }

    /** 
     * Returns all items shared with $userId
     */
    public function findAllShared($limit, $offset, $showAll, $oldestFirst, $userId, $search)
    {
        $sql = 'SELECT `items`.* FROM `*PREFIX*news_items` `items`' .
            'WHERE `shared_with` = ? ';
        $sql .= $this->buildStatusQueryPart($showAll);       
        
        if ($offset !== 0) {
            $sql .= 'AND `items`.`id` ' . $this->getOperator($oldestFirst) . ' ? ';
            $params[] = $offset;
        }

        if ($oldestFirst) {
            $sql .= ' ORDER BY `items`.`id`  ASC';
        } else {
            $sql .= ' ORDER BY `items`.`id` DESC';
        }

        $params = [$userId];

        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }

    /**
     * Returns the count of unread shared items for user $userId
     */
    public function sharedCount(string $userId)
    {
        $sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_items` `items` ' .
            'WHERE `items`.`shared_with` = ? ' .
            'AND `items`.`unread` = 1';

        $params = [$userId];

        $result = $this->execute($sql, $params)->fetch();

        return (int)$result['size'];
    }
    
    public function shareItem($itemId, $shareWithId, $userId)
    {
        // find existing item and copy it
        $item = $this->find($userId, $itemId);

        // copy item
        $newItem = Item::fromImport($item->jsonSerialize());

        // copy/initialize fields
        $newItem->setUnread(true);
        $newItem->setStarred(false);
        $newItem->setFeedId($item->getFeedId());
        $newItem->setFingerprint($item->getFingerprint());
        $newItem->setContentHash($item->getContentHash());
        $newItem->setSearchIndex($item->getSearchIndex());
        
        // set share data
        $newItem->setSharedBy($userId);
        $newItem->setSharedWith($shareWithId);

        // persist new item
        $this->insert($newItem);
    }

    /**
     * Check if the article is already shared between the users
     */
    public function checkSharing($itemId, $shareWithId, $userId)
    {
        $item = $this->find($userId, $itemId);

        $sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_items` `items` ' .
            'WHERE `items`.`shared_by` = ? '.
            'AND `items`.`shared_with` = ?'.
            'AND `items`.`guid_hash` = ?';

            $params = [$userId ,$shareWithId, $item->getGuidHash()];

        $result = $this->execute($sql, $params)->fetch();

        return (int)$result['size'];
    }
}
