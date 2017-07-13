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

namespace OCA\News\Db\Mysql;

use OCA\News\Utility\Time;
use OCP\IDBConnection;

class ItemMapper extends \OCA\News\Db\ItemMapper {

    public function __construct(IDBConnection $db, Time $time){
        parent::__construct($db, $time);
    }


    /**
     * Delete all items for feeds that have over $threshold unread and not
     * starred items
	 * @param int $threshold the number of items that should be deleted
     */
    public function deleteReadOlderThanThreshold($threshold){
        $sql = 'SELECT (COUNT(*) - `feeds`.`articles_per_update`) AS `size`, ' .
        '`feeds`.`id` AS `feed_id`, `feeds`.`articles_per_update` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
                'ON `feeds`.`id` = `items`.`feed_id` ' .
                'AND `items`.`unread` = 0 ' .
                'AND `items`.`starred` = 0 ' .
            'GROUP BY `feeds`.`id`, `feeds`.`articles_per_update` ' .
            'HAVING COUNT(*) > ?';
        $params = [$threshold];
        $result = $this->execute($sql, $params);

        while($row = $result->fetch()) {

            $size = (int) $row['size'];
            $limit = $size - $threshold;

            if($limit > 0) {
                $params = [$row['feed_id'], $limit];

                $sql = 'DELETE FROM `*PREFIX*news_items` ' .
                    'WHERE `items`.`unread` = 0 ' .
                    'AND `items`.`starred` = 0 ' .
                    'AND `feed_id` = ? ' .
                    'ORDER BY `id` ASC ' .
                    'LIMIT ?';

                $this->execute($sql, $params);
            }
        }

    }

    public function readItem($itemId, $isRead, $lastModified, $userId) {
        $item = $this->find($itemId, $userId);

        if ($isRead) {
            $sql = 'UPDATE `*PREFIX*news_items` `items`
                JOIN `*PREFIX*news_feeds` `feeds`
                    ON `feeds`.`id` = `items`.`feed_id`
                SET `items`.`unread` = 0,
                    `items`.`last_modified` = ?
                WHERE `items`.`fingerprint` = ?
                    AND `feeds`.`user_id` = ?';
            $params = [$lastModified, $item->getFingerprint(), $userId];
            $this->execute($sql, $params);
        } else {
            $item->setLastModified($lastModified);
            $item->setUnread(true);
            $this->update($item);
        }
    }

}
