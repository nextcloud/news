<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
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

require_once(__DIR__ . "/../classloader.php");


class Test extends \PHPUnit_Framework_TestCase {

	private $itemMapper;
	private $api;
	private $items;
	
	protected function setUp(){
		$this->api = $this->getMock('\OCA\AppFramework\Core\API', 
			array('prepareQuery'),
			array('a'));
		$this->itemMapper = new ItemMapper($this->api);

		// create mock items
		$item1 = new Item();
		$item1->test = 1;

		$item2 = new Item();
		$item2->test = 2;

		$this->items = array(
			$item1,
			$item2
		);
	}


	public function testFindAllFromFeed(){
		$userId = 'john';
		$feedId = 3;
		$rows = array(
			array('test' => 1), 
			array('test' => 2)
		);
		$sql = 'SELECT * FROM `*PREFIX*news_items` 
			WHERE user_id = ?
			AND feed_id = ?';

		$pdoResult = $this->getMock('Result', 
			array('fetchRow'));
		$pdoResult->expects($this->once())
			->method('fetchRow')
			->will($this->returnValue($rows));

		$query = $this->getMock('Query', 
			array('execute'));
		$query->expects($this->once())
			->method('execute')
			->with($this->equalTo(array($feedId, $userId)))
			->will($this->returnValue($pdoResult));

		$this->api->expects($this->once())
			->method('prepareQuery')
			->with($this->equalTo($sql))
			->will(($this->returnValue($query)));

		$result = $this->itemMapper->findAllFromFeed($feedId, $userId);

		$this->assertEquals($this->items, $result);

	}

}