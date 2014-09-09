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


class FeedTest extends \PHPUnit_Framework_TestCase {


	private function createFeed() {
		$feed = new Feed();
		$feed->setId(3);
		$feed->setUrl('http://google.com/some/weird/path');
		$feed->setTitle('title');
		$feed->setFaviconLink('favicon');
		$feed->setAdded(123);
		$feed->setFolderId(1);
		$feed->setUnreadCount(321);
		$feed->setLink('https://www.google.com/some/weird/path');

		return $feed;
	}

	public function testToAPI() {
		$feed = $this->createFeed();

		$this->assertEquals([
			'id' => 3,
			'url' => 'http://google.com/some/weird/path',
			'title' => 'title',
			'faviconLink' => 'favicon',
			'added' => 123,
			'folderId' => 1,
			'unreadCount' => 321,
			'link' => 'https://www.google.com/some/weird/path'
		], $feed->toAPI());
	}


	public function testSerialize() {
		$feed = $this->createFeed();

		$this->assertEquals([
			'id' => 3,
			'url' => 'http://google.com/some/weird/path',
			'title' => 'title',
			'faviconLink' => 'favicon',
			'added' => 123,
			'folderId' => 1,
			'unreadCount' => 321,
			'link' => 'https://www.google.com/some/weird/path',
			'userId' => null,
			'urlHash' => '44168618f55392b145629d6b3922e84b',
			'preventUpdate' => null,
			'deletedAt' => null,
			'articlesPerUpdate' => null,
			'cssClass' => 'custom-google-com',
		], $feed->jsonSerialize());
	}


	public function testSetXSSUrl() {
		$feed = new Feed();
		$feed->setUrl('javascript:alert()');
		$this->assertEquals('', $feed->getUrl());
	}


	public function testSetUrlUpdatesHash() {
		$feed = new Feed();
		$feed->setUrl('http://test');
		$this->assertEquals(md5('http://test'), $feed->getUrlHash());
	}


	public function testSetXSSLink() {
		$feed = new Feed();
		$feed->setLink('javascript:alert()');
		$this->assertEquals('', $feed->getLink());
	}


}