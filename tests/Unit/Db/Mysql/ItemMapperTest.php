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

use \OCA\News\Db\Item;
use \OCA\News\Db\StatusFlag;
use OCA\News\Utility\Time;

class ItemMapperTest extends  \OCA\News\Tests\Unit\Db\MapperTestUtility {

    private $mapper;
    private $items;
    private $newestItemId;
    private $limit;
    private $user;
    private $offset;
    private $updatedSince;
    private $status;


    public function setUp() {
        parent::setUp();

        $this->mapper = new ItemMapper($this->db, new Time());

        // create mock items
        $item1 = new Item();
        $item2 = new Item();

        $this->items = [$item1, $item2];

        $this->userId = 'john';
        $this->id = 3;
        $this->folderId = 2;

        $this->row = [['id' => $this->items[0]->getId()]];

        $this->rows = [
            ['id' => $this->items[0]->getId()],
            ['id' => $this->items[1]->getId()]
        ];

        $this->user = 'john';
        $this->limit = 10;
        $this->offset = 3;
        $this->id = 11;
        $this->status = 333;
        $this->updatedSince = 323;
        $this->newestItemId = 2;

    }


    public function testDeleteReadOlderThanThresholdDoesNotDeleteBelow(){
        $status = StatusFlag::STARRED | StatusFlag::UNREAD;
        $sql = 'SELECT (COUNT(*) - `feeds`.`articles_per_update`) AS `size`' .
        ', `feeds`.`id` AS `feed_id`, `feeds`.`articles_per_update` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
                'ON `feeds`.`id` = `items`.`feed_id` ' .
                'AND NOT ((`items`.`status` & ?) > 0) ' .
            'GROUP BY `feeds`.`id`, `feeds`.`articles_per_update` ' .
            'HAVING COUNT(*) > ?';

        $threshold = 10;
        $rows = [['feed_id' => 30, 'size' => 9]];
        $params = [$status, $threshold];

        $this->setMapperResult($sql, $params, $rows);
        $this->mapper->deleteReadOlderThanThreshold($threshold);


    }


    public function testDeleteReadOlderThanThreshold(){
        $threshold = 10;
        $status = StatusFlag::STARRED | StatusFlag::UNREAD;

        $sql1 = 'SELECT (COUNT(*) - `feeds`.`articles_per_update`) AS `size`' .
        ', `feeds`.`id` AS `feed_id`, `feeds`.`articles_per_update` ' .
            'FROM `*PREFIX*news_items` `items` ' .
            'JOIN `*PREFIX*news_feeds` `feeds` ' .
                'ON `feeds`.`id` = `items`.`feed_id` ' .
                'AND NOT ((`items`.`status` & ?) > 0) ' .
            'GROUP BY `feeds`.`id`, `feeds`.`articles_per_update` ' .
            'HAVING COUNT(*) > ?';
        $params1 = [$status, $threshold];


        $row = ['feed_id' => 30, 'size' => 11];

        $sql2 = 'DELETE FROM `*PREFIX*news_items` ' .
                    'WHERE NOT ((`status` & ?) > 0) ' .
                    'AND `feed_id` = ? ' .
                    'ORDER BY `id` ASC ' .
                    'LIMIT ?';
        $params2 = [$status, 30, 1];


        $this->setMapperResult($sql1, $params1, [$row]);
        $this->setMapperResult($sql2, $params2);

        $this->mapper->deleteReadOlderThanThreshold($threshold);
    }


}