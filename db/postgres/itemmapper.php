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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;
use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;

use \OCA\News\Db\StatusFlag;


class ItemMapper extends \OCA\News\Db\ItemMapper {

	public function __construct(API $api){
		parent::__construct($api);
	}


	/**
	 * Delete all items for feeds that have over $threshold unread and not
	 * starred items
	 */
	public function deleteReadOlderThanThreshold($threshold){
		$status = StatusFlag::STARRED | StatusFlag::UNREAD;
		$sql = 'SELECT COUNT(*) `size`, `feed_id` ' .
			'FROM `*PREFIX*news_items` ' .
			'WHERE NOT ((`status` & ?) > 0) ' .
			'GROUP BY `feed_id` ' .
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