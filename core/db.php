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

namespace OCA\News\Core;

class Db {


    /**
     * Used to abstract the owncloud database access away
     * @param string $sql the sql query with ? placeholder for params
     * @param int $limit the maximum number of rows
     * @param int $offset from which row we want to start
     * @return \OCP\DB a query object
     */
    public function prepareQuery($sql, $limit=null, $offset=null){
        return \OCP\DB::prepare($sql, $limit, $offset);
    }


    /**
     * Used to get the id of the just inserted element
     * @param string $tableName the name of the table where we inserted the item
     * @return int the id of the inserted element
     */
    public function getInsertId($tableName){
        return \OCP\DB::insertid($tableName);
    }


}
