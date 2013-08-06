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

require_once(__DIR__ . "/../../classloader.php");


class FeedTest extends \PHPUnit_Framework_TestCase {


	public function testToAPI() {
		$feed = new Feed();
		$feed->setId(3);
		$feed->setUrl('http://google');
		$feed->setTitle('title');
		$feed->setFaviconLink('favicon');
		$feed->setAdded(123);
		$feed->setFolderId(1);
		$feed->setUnreadCount(321);
		$feed->setLink('https://google');

		$this->assertEquals(array(
			'id' => 3,
			'url' => 'http://google',
			'title' => 'title',
			'faviconLink' => 'favicon',
			'added' => 123,
			'folderId' => 1,
			'unreadCount' => 321,
			'link' => 'https://google'
			), $feed->toAPI());
	}


	public function testSetXSSUrl() {
		$feed = new Feed();
		$feed->setUrl('javascript:alert()');
		$this->assertEquals('', $feed->getUrl());
	}


	public function testSetXSSLink() {
		$feed = new Feed();
		$feed->setLink('javascript:alert()');
		$this->assertEquals('', $feed->getLink());
	}


}