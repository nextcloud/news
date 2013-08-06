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


	public function testToAPI() {
		$item = new Item();
		$item->setId(3);
		$item->setGuid('guid');
		$item->setGuidHash('hash');
		$item->setUrl('https://google');
		$item->setTitle('title');
		$item->setAuthor('author');
		$item->setPubDate(123);
		$item->setBody('body');
		$item->setEnclosureMime('audio/ogg');
		$item->setEnclosureLink('enclink');
		$item->setFeedId(1);
		$item->setStatus(0);
		$item->setUnread();
		$item->setStarred();
		$item->setLastModified(321);

		$this->assertEquals(array(
			'id' => 3,
			'guid' => 'guid',
			'guidHash' => 'hash',
			'url' => 'https://google',
			'title' => 'title',
			'author' => 'author',
			'pubDate' => 123,
			'body' => 'body',
			'enclosureMime' => 'audio/ogg',
			'enclosureLink' => 'enclink',
			'feedId' => 1,
			'unread' => true,
			'starred' => true,
			'lastModified' => 321
			), $item->toAPI());
	}


	public function testSetAuthor(){
		$item = new Item();
		$item->setAuthor('<a>my link</li>');
		$this->assertEquals('my link', $item->getAuthor());
		$this->assertContains('author', $item->getUpdatedFields());
	}


	public function testSetTitle(){
		$item = new Item();
		$item->setTitle('<a>my link</li>');
		$this->assertEquals('my link', $item->getTitle());
		$this->assertContains('title', $item->getUpdatedFields());
	}


	public function testSetXSSUrl() {
		$item = new Item();
		$item->setUrl('javascript:alert()');
		$this->assertEquals('', $item->getUrl());
	}


}