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
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Class LegacyItemMapper
 *
 * @package OCA\News\Db
 * @deprecated use ItemMapper
 */
class ItemMapper extends Mapper
{

    const TABLE_NAME = 'news_items';
    /**
     * @var Time
     */
    private $time;

    /**
     * NewsMapper constructor.
     *
     * @param IDBConnection $db     Database connection
     * @param Time          $time   Time class
     */
    public function __construct(IDBConnection $db, Time $time)
    {
        parent::__construct($db, static::TABLE_NAME, Item::class);
        $this->time = $time;
    }

    private function makeSelectQuery(
        string $prependTo = '',
        bool $oldestFirst = false,
        bool $distinctFingerprint = false
    ): string {
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

    private function buildSearchQueryPart(array $search = []): string
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
     * @return \OCA\News\Db\Item|Entity
     */
    public function find(string $userId, int $id)
    {
        $sql = $this->makeSelectQuery('AND `items`.`id` = ? ');
        return $this->findEntity($sql, [$userId, $id]);
    }

    public function starredCount(string $userId): int
    {
        $sql = 'SELECT COUNT(*) AS size FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
            'ON `feeds`.`id` = `items`.`feed_id` ' .
            'AND `feeds`.`deleted_at` = 0 ' .
            'AND `feeds`.`user_id` = ? ' .
            'AND `items`.`starred` = ? ' .
            'LEFT OUTER JOIN `*PREFIX*news_folders` `folders` ' .
            'ON `folders`.`id` = `feeds`.`folder_id` ' .
            'WHERE `feeds`.`folder_id` IS NULL ' .
            'OR `folders`.`deleted_at` = 0';

        $params = [$userId, true];

        $result = $this->execute($sql, $params)->fetch();

        return (int)$result['size'];
    }


    public function readAll(int $highestItemId, string $time, string $userId): void
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


    public function readFolder(?int $folderId, int $highestItemId, string $time, string $userId): void
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


    public function readFeed(int $feedId, int $highestItemId, string $time, string $userId): void
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


    private function getOperator(bool $oldestFirst): string
    {
        if ($oldestFirst) {
            return '>';
        } else {
            return '<';
        }
    }


    public function findAllNew(int $updatedSince, int $type, bool $showAll, string $userId): array
    {
        $sql = $this->buildStatusQueryPart($showAll, $type);

        $sql .= 'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFolder(?int $id, int $updatedSince, bool $showAll, string $userId): array
    {
        $sql = $this->buildStatusQueryPart($showAll);

        $folderWhere = is_null($id) ? 'IS' : '=';
        $sql .= "AND `feeds`.`folder_id` ${folderWhere} ? " .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    public function findAllNewFeed(?int $id, int $updatedSince, bool $showAll, string $userId): array
    {
        $sql = $this->buildStatusQueryPart($showAll);

        $sql .= 'AND `items`.`feed_id` = ? ' .
            'AND `items`.`last_modified` >= ? ';
        $sql = $this->makeSelectQuery($sql);
        $params = [$userId, $id, $updatedSince];
        return $this->findEntities($sql, $params);
    }


    /**
     * @param (int|mixed|null)[] $params
     */
    private function findEntitiesIgnoringNegativeLimit(string $sql, array $params, int $limit): array
    {
        // ignore limit if negative to offer a way to return all feeds
        if ($limit >= 0) {
            return $this->findEntities($sql, $params, $limit);
        } else {
            return $this->findEntities($sql, $params);
        }
    }


    public function findAllFeed(
        ?int $id,
        int $limit,
        int $offset,
        bool $showAll,
        bool $oldestFirst,
        string $userId,
        array $search = []
    ): array {
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
        $sql = $this->makeSelectQuery($sql, $oldestFirst);
        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    public function findAllFolder(
        ?int $id,
        int $limit,
        int $offset,
        bool $showAll,
        bool $oldestFirst,
        string $userId,
        array $search = []
    ): array {
        $params = [$userId];
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
        $sql = $this->makeSelectQuery($sql, $oldestFirst);
        return $this->findEntitiesIgnoringNegativeLimit($sql, $params, $limit);
    }


    /**
     * @param string[] $search
     */
    public function findAllItems(
        int $limit,
        int $offset,
        int $type,
        bool $showAll,
        bool $oldestFirst,
        string $userId,
        array $search = []
    ): array {
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


    public function findAllUnreadOrStarred(string $userId): array
    {
        $params = [$userId, true, true];
        $sql = 'AND (`items`.`unread` = ? OR `items`.`starred` = ?) ';
        $sql = $this->makeSelectQuery($sql);
        return $this->findEntities($sql, $params);
    }

    /**
     * @param $guidHash
     * @param $feedId
     * @param $userId
     *
     * @return Entity|Item
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findByGuidHash($guidHash, $feedId, $userId)
    {
        $sql = $this->makeSelectQuery(
            'AND `items`.`guid_hash` = ? ' .
            'AND `feeds`.`id` = ? '
        );

        return $this->findEntity($sql, [$userId, $guidHash, $feedId]);
    }


    /**
     * Delete all items for feeds that have over $threshold unread and not
     * starred items
     *
     * @param int $threshold the number of items that should be deleted
     *
     * @return void
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


    public function getNewestItemId(string $userId): int
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
     * Returns a list of ids and userid of all items
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array|false
     */
    public function findAllIds(?int $limit = null, ?int $offset = null)
    {
        $sql = 'SELECT `id` FROM `*PREFIX*news_items`';
        return $this->execute($sql, [], $limit, $offset)->fetchAll();
    }

    /**
     * Update search indices of all items
     *
     * @return void
     */
    public function updateSearchIndices(): void
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

    private function updateSearchIndex(array $items = []): void
    {
        foreach ($items as $row) {
            $sql = 'SELECT * FROM `*PREFIX*news_items` WHERE `id` = ?';
            $params = [$row['id']];
            $item = $this->findEntity($sql, $params);
            $item->generateSearchIndex();
            $this->update($item);
        }
    }

    /**
     * @return void
     */
    public function readItem(int $itemId, bool $isRead, string $lastModified, string $userId)
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
                            WHERE `f`.`user_id` = ?
                    )';
            $params = [false, $lastModified, $item->getFingerprint(), $userId];
            $this->execute($sql, $params);
        } else {
            $item->setLastModified($lastModified);
            $item->setUnread(true);
            $this->update($item);
        }
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
     * Remove deleted items.
     *
     * @return void
     */
    public function purgeDeleted(): void
    {
        $builder = $this->db->getQueryBuilder();
        $builder->delete($this->tableName)
            ->where('deleted_at != 0')
            ->execute();
    }
    /**
     * Performs a SELECT query with all arguments appended to the WHERE clause
     * The SELECT will be performed on the current table and takes the entity
     * that is related for transforming the properties into column names
     *
     * Important: This method does not filter marked as deleted rows!
     *
     * @param array $search an assoc array from property to filter value
     * @param int|null $limit  Output limit
     * @param int|null $offset Output offset
     *
     * @depreacted Legacy function
     *
     * @return Entity[]
     */
    public function where(array $search = [], ?int $limit = null, ?int $offset = null)
    {
        $entity = new $this->entityClass();

        // turn keys into sql query filter, e.g. feedId -> feed_id = :feedId
        $filter = array_map(
            function ($property) use ($entity) {
                // check if the property actually exists on the entity to prevent
                // accidental Sql injection
                if (!property_exists($entity, $property)) {
                    $msg = 'Property ' . $property . ' does not exist on '
                        . $this->entityClass;
                    throw new \BadFunctionCallException($msg);
                }

                $column = $entity->propertyToColumn($property);
                return $column . ' = :' . $property;
            },
            array_keys($search)
        );

        $andStatement = implode(' AND ', $filter);

        $sql = 'SELECT * FROM `' . $this->getTableName() . '`';

        if (count($search) > 0) {
            $sql .= 'WHERE ' . $andStatement;
        }

        return $this->findEntities($sql, $search, $limit, $offset);
    }
}
