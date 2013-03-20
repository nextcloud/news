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


class ItemTest extends \PHPUnit_Framework_TestCase {

	private $item;

	protected function setUp(){
		$this->item = new Item();
		$this->item->setStatus(0);
	}


	public function testSetRead(){
		$this->item->setRead();

		$this->assertTrue($this->item->isRead());
	}


	public function testSetUnread(){
		$this->item->setUnread();

		$this->assertTrue($this->item->isUnread());
	}


	public function testSetStarred(){
		$this->item->setStarred();

		$this->assertTrue($this->item->isStarred());
	}


	public function testSetUnstarred(){
		$this->item->setUnstarred();

		$this->assertTrue($this->item->isUnstarred());
	}


}