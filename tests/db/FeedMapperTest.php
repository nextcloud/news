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


class FeedMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $feedMapper;
	private $feeds;
	
	protected function setUp(){
		$this->beforeEach();

		$this->feedMapper = new FeedMapper($this->api);

		// create mock feeds
		$feed1 = new Feed();
		$feed2 = new Feed();

		$this->feeds = array(
			$feed1,
			$feed2
		);
	}


	public function testFind(){
		$userId = 'john';
		$id = 3;
		$rows = array(
		  array('id' => $this->feeds[0]->getId()),
		);
		$sql = 'SELECT * FROM `*dbprefix*news_feeds` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$result = $this->feedMapper->find($id, $userId);
		$this->assertEquals($this->feeds[0], $result);
		
	}


	public function testFindNotFound(){
		$userId = 'john';
		$id = 3;
		$sql = 'SELECT * FROM `*dbprefix*news_feeds` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId));
		
		$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');
		$result = $this->feedMapper->find($id, $userId);	
	}
	

	public function testFindMoreThanOneResultFound(){
		$userId = 'john';
		$id = 3;
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT * FROM `*dbprefix*news_feeds` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$this->setExpectedException('\OCA\AppFramework\Db\MultipleObjectsReturnedException');
		$result = $this->feedMapper->find($id, $userId);
	}


	public function testFindAll(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT * FROM `*dbprefix*news_feeds`';
		
		$this->setMapperResult($sql, array(), $rows);
		
		$result = $this->feedMapper->findAll();
		$this->assertEquals($this->feeds, $result);
	}


	public function testFindAllFromUser(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->feeds[0]->getId()),
			array('id' => $this->feeds[1]->getId())
		);
		$sql = 'SELECT * FROM `*dbprefix*news_feeds` ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($userId), $rows);
		
		$result = $this->feedMapper->findAllFromUser($userId);
		$this->assertEquals($this->feeds, $result);
	}

}