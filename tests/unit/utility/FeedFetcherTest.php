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

namespace OCA\News\Utility;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;

require_once(__DIR__ . "/../../classloader.php");


class FeedFetcherTest extends \OCA\AppFramework\Utility\TestUtility {

	private $fetcher;
	private $core;
	private $coreFactory;
	private $faviconFetcher;
	private $url;
	private $cacheDirectory;
	private $cacheDuration;
	private $time;
	private $item;

	// items
	private $permalink;
	private $title;
	private $guid;
	private $pub;
	private $body;
	private $author;
	private $enclosureLink;

	// feed
	private $feedTitle;
	private $feedLink;
	private $feedImage;
	private $webFavicon;

	protected function setUp(){
		$this->core = $this->getMockBuilder(
			'\SimplePie_Core')
			->disableOriginalConstructor()
			->getMock();
		$this->coreFactory = $this->getMockBuilder(
			'\OCA\AppFramework\Utility\SimplePieAPIFactory')
			->disableOriginalConstructor()
			->getMock();
		$this->coreFactory->expects($this->any())
			->method('getCore')
			->will($this->returnValue($this->core));
		$this->item = $this->getMockBuilder(
			'\SimplePie_Item')
			->disableOriginalConstructor()
			->getMock();
		$this->faviconFetcher = $this->getMockBuilder(
			'\OCA\AppFramework\Utility\FaviconFetcher')
			->disableOriginalConstructor()
			->getMock();
		$this->time = 2323;
		$timeFactory = $this->getMockBuilder(
			'\OCA\AppFramework\Utility\TimeFactory')
			->disableOriginalConstructor()
			->getMock();
		$timeFactory->expects($this->any())
			->method('getTime')
			->will($this->returnValue($this->time));
		$this->cacheDuration = 100;
		$this->cacheDirectory = 'dir/';
		$this->fetcher = new FeedFetcher($this->getAPIMock(), 
		                                 $this->coreFactory,
		                                 $this->faviconFetcher, 
		                                 $timeFactory,
		                                 $this->cacheDirectory,
		                                 $this->cacheDuration);
		$this->url = 'tests';

		$this->permalink = 'http://permalink';
		$this->title = 'my title&lt;';
		$this->guid = 'hey guid here';
		$this->body = 'let the bodies hit the floor';
		$this->pub = 23111;
		$this->author = '&lt;boogieman';
		$this->enclosureLink = 'http://enclosure.you';

		$this->feedTitle = '&lte;its a title';
		$this->feedLink = 'http://goatse';
		$this->feedImage = '/an/image';
		$this->webFavicon = 'http://anon.google.com';
	}


	public function testCanHandle(){
		$url = 'google.de';

		$this->assertTrue($this->fetcher->canHandle($url));
	}


	public function testFetchThrowsExceptionWhenInitFailed() {
		$this->core->expects($this->once())
			->method('set_feed_url')
			->with($this->equalTo($this->url));
		$this->core->expects($this->once())
			->method('enable_cache')
			->with($this->equalTo(true));
		$this->core->expects($this->once())
			->method('set_cache_location')
			->with($this->equalTo($this->cacheDirectory));
		$this->core->expects($this->once())
			->method('set_cache_duration')
			->with($this->equalTo($this->cacheDuration));
		$this->setExpectedException('\OCA\News\Utility\FetcherException');
		$this->fetcher->fetch($this->url);
	}
	

	public function testShouldCatchExceptionsAndThrowOwnException() {
		$this->core->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$this->core->expects($this->once())
			->method('get_items')
			->will($this->throwException(new \Exception('oh noes!')));
		$this->setExpectedException('\OCA\News\Utility\FetcherException');
		$this->fetcher->fetch($this->url);
	}


	private function expectCore($method, $return) {
		$this->core->expects($this->once())
			->method($method)
			->will($this->returnValue($return));
	}

	private function expectItem($method, $return) {
		$this->item->expects($this->once())
			->method($method)
			->will($this->returnValue($return));
	}

 
	private function createItem($author=false, $enclosureType=null) {
		$this->expectItem('get_permalink', $this->permalink);
		$this->expectItem('get_title', $this->title);
		$this->expectItem('get_id', $this->guid);
		$this->expectItem('get_content', $this->body);
		$this->expectItem('get_date', $this->pub);

		$item = new Item();
		$item->setStatus(0);
		$item->setUnread();
		$item->setUrl($this->permalink);
		$item->setTitle(html_entity_decode($this->title));
		$item->setGuid($this->guid);
		$item->setGuidHash(md5($this->guid));
		$item->setBody($this->body);
		$item->setPubDate($this->pub);
		$item->setLastModified($this->time);
		if($author) {
			$mock = $this->getMock('author', array('get_name'));
			$mock->expects($this->once())
				->method('get_name')
				->will($this->returnValue($this->author));
			$this->expectItem('get_author', $mock);
			$item->setAuthor(html_entity_decode($this->author));
		}

		if($enclosureType === 'audio/ogg') {
			$mock = $this->getMock('enclosure', array('get_type', 'get_link'));
			$mock->expects($this->any())
				->method('get_type')
				->will($this->returnValue($enclosureType));
			$this->expectItem('get_enclosure', $this->mock);
			$item->setEnclosureMime($enclosureType);
			$item->setEnclosureLink($this->enclosureLink);
		}
		return $item;
	}


	private function createFeed($hasFavicon=false, $hasWebFavicon=false) {
		$this->expectCore('get_title', $this->feedTitle);
		$this->expectCore('get_link', $this->feedLink);

		$feed = new Feed();
		$feed->setTitle(html_entity_decode($this->feedTitle));
		$feed->setUrl($this->url);
		$feed->setLink($this->feedLink);
		$feed->setUrlHash(md5($this->url));
		$feed->setAdded($this->time);

		if($hasFavicon) {
			$this->expectCore('get_image_url', $this->feedImage);
			$feed->setFaviconLink($this->feedImage);
		} else {
			$feed->setFaviconLink(null);
			$this->expectCore('get_image_url', null);
		}

		if($hasWebFavicon) {
			$this->faviconFetcher->expects($this->once())
				->method('fetch')
				->with($this->equalTo($this->feedLink))
				->will($this->returnValue($this->webFavicon));
			$feed->setFaviconLink($this->webFavicon);
		}

		return $feed;
	}


	public function testFetchMapItems(){
		$this->core->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$item = $this->createItem();
		$feed = $this->createFeed();
		$this->expectCore('get_items', array($this->item));
		$result = $this->fetcher->fetch($this->url);

		$this->assertEquals(array($feed, array($item)), $result);
	}


	public function testFetchMapItemsNoFeedTitleUsesUrl(){
		$this->expectCore('get_title', '');
		$this->expectCore('get_link', $this->feedLink);

		$feed = new Feed();
		$feed->setTitle($this->url);
		$feed->setUrl($this->url);
		$feed->setLink($this->feedLink);
		$feed->setUrlHash(md5($this->url));
		$feed->setAdded($this->time);
		$feed->setFaviconLink(null);

		$this->core->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$item = $this->createItem();
		$this->expectCore('get_items', array($this->item));
		$result = $this->fetcher->fetch($this->url);

		$this->assertEquals(array($feed, array($item)), $result);
	}

	public function testFetchMapItemsAuthorExists(){
		$this->core->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$item = $this->createItem(true);
		$feed = $this->createFeed(true);
		$this->expectCore('get_items', array($this->item));
		$result = $this->fetcher->fetch($this->url);

		$this->assertEquals(array($feed, array($item)), $result);
	}


	public function testFetchMapItemsEnclosureExists(){
		$this->core->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$item = $this->createItem(false, true);
		$feed = $this->createFeed(false, true);
		$this->expectCore('get_items', array($this->item));
		$result = $this->fetcher->fetch($this->url);

		$this->assertEquals(array($feed, array($item)), $result);
	}

}
