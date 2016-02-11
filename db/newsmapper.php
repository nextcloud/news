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

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

abstract class NewsMapper extends Mapper {

    public function __construct(IDBConnection $db, $table, $entity) {
        parent::__construct($db, $table, $entity);
    }

    /**
     * @param int $id the id of the feed
     * @param string $userId the id of the user
     * @return \OCP\AppFramework\Db\Entity
     */
    abstract public function find($id, $userId);

    /**
     * Performs a SELECT query with all arguments appened to the WHERE clause
     * The SELECT will be performed on the current table and take the entity
     * that is related for transforming the properties into column names
     *
     * Important: This method does not filter marked as deleted rows!
     *
     * @param array $search an assoc array from property to filter value
     * @param int $limit
     * @paran int $offset
     * @return array
     */
    public function where(array $search=[], $limit=null, $offset=null) {
        $entity = new $this->entityClass;

        // turn keys into sql query filter, e.g. feedId -> feed_id = :feedId
        $filter = array_map(function ($property) use ($entity) {
            // check if the property actually exists on the entity to prevent
            // accidental Sql injection
            if (!property_exists($entity, $property)) {
                $msg = 'Property ' . $property . ' does not exist on '
                    . $this->entityClass;
                throw new \BadFunctionCallException($msg);
            }

            $column = $entity->propertyToColumn($property);
            return $column . ' = :' . $property;
        }, array_keys($search));

        $andStatement = implode(' AND ', $filter);

        $sql = 'SELECT * FROM `' . $this->getTableName() . '`';

        if (count($search) > 0) {
            $sql .= 'WHERE ' . $andStatement;
        }

        return $this->findEntities($sql, $search, $limit, $offset);
    }

}
