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

class StatusFlag {
	const UNREAD    = 0x02;
	const STARRED   = 0x04;
	const DELETED   = 0x08;
	const UPDATED   = 0x16;


	/**
	 * Get status for query
	 */
	public function typeToStatus($type, $showAll){
		if($type === FeedType::STARRED){
			return self::STARRED;
		} else {
			$status = 0;
		}

		if($showAll){
			$status &= ~self::UNREAD;
		} else {
			$status |= self::UNREAD;
		}

		return $status;
	}


}