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