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


	protected function assertSetterGetter($name){
		$value = 'value';

		$item = new Item();
		$setMethod = 'set' . $name;
		$getMethod = 'get' . $name;
		$item->$setMethod($value);

		$this->assertEquals($value, $item->$getMethod());
	}


	public function testGetUrl(){
		$this->assertSetterGetter('Url');
	}


	public function testSetFeedId(){
		$this->assertSetterGetter('FeedId');
	}


	public function testSetGUID(){
		$this->assertSetterGetter('GUID');
	}


	public function testSetStatus(){
		$this->assertSetterGetter('Status');
	}


	public function testSetTitle(){
		$this->assertSetterGetter('Title');
	}


	public function testSetFeedTitle(){
		$this->assertSetterGetter('FeedTitle');
	}

}