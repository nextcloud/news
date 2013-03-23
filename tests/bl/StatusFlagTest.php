<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

namespace OCA\News\Db;

use \OCA\AppFramework\Utility\TestUtility;


require_once(__DIR__ . "/../classloader.php");


class StatusFlagTest extends TestUtility {

	private $statusFlag;

	protected function setUp(){
		$this->statusFlag = new StatusFlag();
	}


	public function testTypeToStatusUnreadStarred(){
		$expected = StatusFlag::UNREAD | StatusFlag::STARRED;
		$status = $this->statusFlag->typeToStatus(FeedType::STARRED, true);

		$this->assertEquals($expected, $status);
	}


	public function testTypeToStatusUnread(){
		$expected = StatusFlag::UNREAD;
		$status = $this->statusFlag->typeToStatus(FeedType::FEED, true);

		$this->assertEquals($expected, $status);
	}


	public function testTypeToStatusReadStarred(){
		$expected = (~StatusFlag::UNREAD) & StatusFlag::STARRED;
		$status = $this->statusFlag->typeToStatus(FeedType::STARRED, false);

		$this->assertEquals($expected, $status);
	}


	public function testTypeToStatusRead(){
		$expected = (~StatusFlag::UNREAD) & 0;
		$status = $this->statusFlag->typeToStatus(FeedType::FEED, false);

		$this->assertEquals($expected, $status);
	}

}