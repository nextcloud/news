<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Db\Postgres;

use \OCA\News\Core\Db;
use \OCA\News\Db\DoesNotExistException;
use \OCA\News\Db\MultipleObjectsReturnedException;
use \OCA\News\Db\Mapper;
use \OCA\News\Db\StatusFlag;


class ItemMapper extends \OCA\News\Db\ItemMapper {

	public function __construct(Db $db){
		parent::__construct($db, 'news_items', '\OCA\News\Db\Item');
	}


	/**
	 * Delete all items for feeds that have over $threshold unread and not
	 * starred items
	 */
	public function deleteReadOlderThanThreshold($threshold){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT COUNT(*) - `feeds`.`articles_per_update` AS `size`, ' .
		'`items`.`feed_id` AS `feed_id` ' . 
			'FROM `*PREFIX*news_items` `items` ' .
			'JOIN `*PREFIX*news_feeds` `feeds` ' .
				'ON `feeds`.`id` = `items`.`feed_id` ' .
			'WHERE NOT ((`items`.`status` & ?) > 0) ' .
			'GROUP BY `items`.`feed_id`, `feeds`.`articles_per_update` ' .
			'HAVING COUNT(*) > ?';
		$params = array($status, $threshold);
		$result = $this->execute($sql, $params);

		while($row = $result->fetchRow()) {

			$size = (int) $row['size'];
			$limit = $size - $threshold;

			if($limit > 0) {
				$params = array($status, $row['feed_id'], $limit);

				$sql = 'DELETE FROM `*PREFIX*news_items` ' .
				'WHERE `id` IN (' .
					'SELECT `id` FROM `*PREFIX*news_items` ' .
					'WHERE NOT ((`status` & ?) > 0) ' .
					'AND `feed_id` = ? ' .
					'ORDER BY `id` ASC ' .
					'LIMIT ?' .
				')';

				$this->execute($sql, $params);
			}
		}

	}


}