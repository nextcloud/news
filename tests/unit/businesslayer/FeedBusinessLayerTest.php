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


namespace OCA\News\BusinessLayer;

require_once(__DIR__ . "/../../classloader.php");

use \OCA\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use \OCA\News\Utility\Fetcher;
use \OCA\News\Utility\FetcherException;
use \OCA\News\Utility\ImportParser;

class FeedBusinessLayerTest extends \OCA\AppFramework\Utility\TestUtility {

	private $feedMapper;
	private $feedBusinessLayer;
	private $user;
	private $response;
	private $fetcher;
	private $itemMapper;
	private $threshold;
	private $time;
	private $importParser;
	private $autoPurgeMinimumInterval;

	protected function setUp(){
		$this->api = $this->getAPIMock();
		$this->time = 222;
		$this->autoPurgeMinimumInterval = 10;
		$timeFactory = $this->getMockBuilder(
			'\OCA\AppFramework\Utility\TimeFactory')
			->disableOriginalConstructor()
			->getMock();
		$timeFactory->expects($this->any())
			->method('getTime')
			->will($this->returnValue($this->time));
		$this->feedMapper = $this->getMockBuilder('\OCA\News\Db\FeedMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->fetcher = $this->getMockBuilder('\OCA\News\Utility\Fetcher')
			->disableOriginalConstructor()
			->getMock();
		$this->itemMapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->importParser = $this->getMockBuilder('\OCA\News\Utility\ImportParser')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = new FeedBusinessLayer($this->feedMapper,
			$this->fetcher, $this->itemMapper, $this->api,
			$timeFactory, $this->importParser, $this->autoPurgeMinimumInterval);
		$this->user = 'jack';
		$response = 'hi';
	}


	public function testFindAll(){
		$this->feedMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->feedBusinessLayer->findAll($this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testCreateDoesNotFindFeed(){
		$ex = new FetcherException('hi');
		$url = 'test';
		$trans = $this->getMock('Trans', array('t'));
		$trans->expects($this->once())
			->method('t');
		$this->api->expects($this->once())
			->method('getTrans')
			->will($this->returnValue($trans));
		$this->feedMapper->expects($this->once())
			->method('findByUrlHash')
			->with($this->equalTo(md5($url)), $this->equalTo($this->user))
			->will($this->throwException(new DoesNotExistException('yo')));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->throwException($ex));
		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->feedBusinessLayer->create($url, 1, $this->user);
	}

	public function testCreate(){
		$url = 'test';
		$folderId = 10;
		$createdFeed = new Feed();
		$ex = new DoesNotExistException('yo');
		$createdFeed->setUrl($url);
		$item1 = new Item();
		$item1->setGuidHash('hi');
		$item2 = new Item();
		$item2->setGuidHash('yo');
		$return = array(
			$createdFeed,
			array($item1, $item2)
		);

		$this->feedMapper->expects($this->once())
			->method('findByUrlHash')
			->with($this->equalTo(md5($url)), $this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->returnValue($return));
		$this->feedMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($createdFeed))
			->will($this->returnValue($createdFeed));
		$this->itemMapper->expects($this->at(0))
			->method('findByGuidHash')
			->with(
				$this->equalTo($item2->getGuidHash()),
				$this->equalTo($item2->getFeedId()),
				$this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->itemMapper->expects($this->at(1))
			->method('insert')
			->with($this->equalTo($return[1][1]));
		$this->itemMapper->expects($this->at(2))
			->method('findByGuidHash')
			->with(
				$this->equalTo($item1->getGuidHash()),
				$this->equalTo($item1->getFeedId()),
				$this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->itemMapper->expects($this->at(3))
			->method('insert')
			->with($this->equalTo($return[1][0]));

		$feed = $this->feedBusinessLayer->create($url, $folderId, $this->user);

		$this->assertEquals($feed->getFolderId(), $folderId);
		$this->assertEquals($feed->getUrl(), $url);
	}


	public function testCreateItemGuidExistsAlready(){
		$url = 'test';
		$folderId = 10;
		$createdFeed = new Feed();
		$ex = new DoesNotExistException('yo');
		$createdFeed->setUrl($url);
		$item1 = new Item();
		$item1->setGuidHash('hi');
		$item2 = new Item();
		$item2->setGuidHash('yo');
		$return = array(
			$createdFeed,
			array($item1, $item2)
		);

		$this->feedMapper->expects($this->once())
			->method('findByUrlHash')
			->with($this->equalTo(md5($url)), $this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->returnValue($return));
		$this->feedMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($createdFeed))
			->will($this->returnValue($createdFeed));
		$this->itemMapper->expects($this->at(0))
			->method('findByGuidHash')
			->with(
				$this->equalTo($item2->getGuidHash()),
				$this->equalTo($item2->getFeedId()),
				$this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->itemMapper->expects($this->at(1))
			->method('insert')
			->with($this->equalTo($return[1][1]));
		$this->itemMapper->expects($this->at(2))
			->method('findByGuidHash')
			->with(
				$this->equalTo($item1->getGuidHash()),
				$this->equalTo($item1->getFeedId()),
				$this->equalTo($this->user));

		$feed = $this->feedBusinessLayer->create($url, $folderId, $this->user);

		$this->assertEquals($feed->getFolderId(), $folderId);
		$this->assertEquals($feed->getUrl(), $url);
		$this->assertEquals(1, $feed->getUnreadCount());
	}


	public function testUpdateCreatesNewEntry(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$item = new Item();
		$item->setGuidHash(md5('hi'));
		$item->setFeedId(3);
		$items = array(
			$item
		);

		$ex = new DoesNotExistException('hi');

		$fetchReturn = array($feed, $items);

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->returnValue($fetchReturn));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($items[0]->getGuidHash()),
					$this->equalTo($items[0]->getFeedId()),
					$this->equalTo($this->user))
			->will($this->throwException($ex));
		$this->itemMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($items[0]));

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($feed->getId(), $this->user)
			->will($this->returnValue($feed));

		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);

		$this->assertEquals($return, $feed);
	}


	public function testUpdateUpdatesEntryNotWhenPubDateSame(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$item = new Item();
		$item->setGuidHash(md5('hi'));
		$item->setPubDate(3333);
		$items = array(
			$item
		);

		$fetchReturn = array($feed, $items);

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->returnValue($fetchReturn));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($item->getGuidHash()),
					$this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($item));
		$this->itemMapper->expects($this->never())
			->method('insert');
		$this->itemMapper->expects($this->never())
			->method('delete');

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($feed->getId(), $this->user)
			->will($this->returnValue($feed));

		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);

		$this->assertEquals($return, $feed);
	}


	public function testUpdateUpdatesEntryNotWhenPubDateUnkown(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$item = new Item();
		$item->setGuidHash(md5('hi'));
		$item->setPubDate(false);
		$items = array(
			$item
		);

		$item2 = new Item();
		$item2->setPubDate(0);

		$fetchReturn = array($feed, $items);

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->returnValue($fetchReturn));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($item->getGuidHash()),
				$this->equalTo($feed->getId()),
				$this->equalTo($this->user))
			->will($this->returnValue($item2));
		$this->itemMapper->expects($this->never())
			->method('insert');
		$this->itemMapper->expects($this->never())
			->method('delete');

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($feed->getId(), $this->user)
			->will($this->returnValue($feed));

		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);

		$this->assertEquals($return, $feed);
	}

	public function testUpdateUpdatesEntryNotWhenNoPubDate(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$item = new Item();
		$item->setGuidHash(md5('hi'));
		$item->setPubDate(null);
		$items = array(
			$item
		);

		$item2 = new Item();
		$item2->setPubDate(null);

		$fetchReturn = array($feed, $items);

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->returnValue($fetchReturn));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($item->getGuidHash()),
				$this->equalTo($feed->getId()),
				$this->equalTo($this->user))
			->will($this->returnValue($item2));
		$this->itemMapper->expects($this->never())
			->method('insert');
		$this->itemMapper->expects($this->never())
			->method('delete');

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($feed->getId(), $this->user)
			->will($this->returnValue($feed));

		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);

		$this->assertEquals($return, $feed);
	}


	public function testCreateUpdateFails(){
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');
		$ex = new FetcherException('');

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->throwException($ex));
		$this->api->expects($this->any())
			->method('log');

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($feed->getId(), $this->user)
			->will($this->returnValue($feed));

		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);

		$this->assertEquals($return, $feed);
	}


	public function testUpdateDoesNotFindEntry() {
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$ex = new DoesNotExistException('');

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);
	}


	public function testUpdateDoesNotFindUpdatedEntry() {
		$feed = new Feed();
		$feed->setId(3);
		$feed->getUrl('test');

		$item = new Item();
		$item->setGuidHash(md5('hi'));
		$item->setPubDate(3333);
		$item->setId(4);
		$items = array(
			$item
		);

		$item2 = new Item();
		$item2->setPubDate(111);

		$fetchReturn = array($feed, $items);
		$ex = new DoesNotExistException('');

		$this->feedMapper->expects($this->at(0))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->once())
			->method('fetch')
			->will($this->returnValue($fetchReturn));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($item->getGuidHash()),
					$this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->returnValue($item2));;

		$this->feedMapper->expects($this->at(1))
			->method('find')
			->with($this->equalTo($feed->getId()),
					$this->equalTo($this->user))
			->will($this->throwException($ex));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$return = $this->feedBusinessLayer->update($feed->getId(), $this->user);
	}


	public function testUpdateDoesntUpdateIfFeedIsPrevented() {
		$feedId = 3;
		$folderId = 4;
		$feed = new Feed();
		$feed->setFolderId(16);
		$feed->setId($feedId);
		$feed->setPreventUpdate(true);

		$this->feedMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($feedId),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->fetcher->expects($this->never())
			->method('fetch');

		$this->feedBusinessLayer->update($feedId, $this->user);
	}


	public function testMove(){
		$feedId = 3;
		$folderId = 4;
		$feed = new Feed();
		$feed->setFolderId(16);
		$feed->setId($feedId);

		$this->feedMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($feedId), $this->equalTo($this->user))
			->will($this->returnValue($feed));

		$this->feedMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($feed));

		$this->feedBusinessLayer->move($feedId, $folderId, $this->user);

		$this->assertEquals($folderId, $feed->getFolderId());
	}


	public function testImportGoogleReaderJSON(){
		$url = 'http://owncloud/googlereader';
		$urlHash = md5($url);

		$feed = new Feed();
		$feed->setId(3);
		$feed->setUserId($this->user);
		$feed->setUrlHash($urlHash);
		$feed->setUrl($url);
		$feed->setTitle('Google Reader');
		$feed->setAdded($this->time);
		$feed->setFolderId(0);
		$feed->setPreventUpdate(true);

		$items = array(new Item());

		$this->feedMapper->expects($this->at(0))
			->method('findByUrlHash')
			->with($this->equalTo($urlHash),
				$this->equalTo($this->user))
			->will($this->throwException(new DoesNotExistException('hi')));
		$this->feedMapper->expects($this->at(1))
			->method('insert')
			->will($this->returnValue($feed));
		$this->feedMapper->expects($this->at(2))
			->method('findByUrlHash')
			->with($this->equalTo($urlHash),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->importParser->expects($this->once())
			->method('parse')
			->will($this->returnValue($items));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash');
		$this->itemMapper->expects($this->never())
			->method('insert');


		$result = $this->feedBusinessLayer->importGoogleReaderJSON(array(), $this->user);

		$this->assertEquals($feed, $result);
	}


	public function testImportGoogleReaderJSONFeedExists(){
		$url = 'http://owncloud/googlereader';
		$urlHash = md5($url);


		$feed = new Feed();
		$feed->setUserId($this->user);
		$feed->setUrlHash($urlHash);
		$feed->setUrl($url);
		$feed->setTitle('Google Reader');
		$feed->setAdded($this->time);
		$feed->setFolderId(0);
		$feed->setPreventUpdate(true);
		$feed->setId(3);

		$item = new Item();
		$item->setGuidHash('hi');
		$items = array($item);
		$savedItem = new Item();
		$savedItem->setFeedId($feed->getId());
		$savedItem->setGuidHash('hi');

		$in = array();

		$this->feedMapper->expects($this->at(0))
			->method('findByUrlHash')
			->with($this->equalTo($urlHash),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->feedMapper->expects($this->at(1))
			->method('findByUrlHash')
			->with($this->equalTo($urlHash),
				$this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->importParser->expects($this->once())
			->method('parse')
			->with($this->equalTo($in))
			->will($this->returnValue($items));
		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->with($this->equalTo($savedItem->getGuidHash()),
				$this->equalTo($savedItem->getFeedId()),
				$this->equalTo($this->user))
			->will($this->throwException(new DoesNotExistException('ho')));
		$this->itemMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($savedItem));

		$result = $this->feedBusinessLayer->importGoogleReaderJSON($in, $this->user);

		$this->assertEquals($feed, $result);
	}


	public function testMarkDeleted() {
		$id = 3;
		$feed = new Feed();
		$feed2 = new Feed();
		$feed2->setDeletedAt($this->time);

		$this->feedMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->feedMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($feed2));

		$this->feedBusinessLayer->markDeleted($id, $this->user);
	}


	public function testUnmarkDeleted() {
		$id = 3;
		$feed = new Feed();
		$feed2 = new Feed();
		$feed2->setDeletedAt(0);

		$this->feedMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->returnValue($feed));
		$this->feedMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($feed2));

		$this->feedBusinessLayer->unmarkDeleted($id, $this->user);
	}


	public function testPurgeDeleted(){
		$feed1 = new Feed();
		$feed1->setId(3);
		$feed2 = new Feed();
		$feed2->setId(5);
		$feeds = array($feed1, $feed2);

		$time = $this->time - $this->autoPurgeMinimumInterval;
		$this->feedMapper->expects($this->once())
			->method('getToDelete')
			->with($this->equalTo($time), $this->equalTo($this->user))
			->will($this->returnValue($feeds));
		$this->feedMapper->expects($this->at(1))
			->method('delete')
			->with($this->equalTo($feed1));
		$this->feedMapper->expects($this->at(2))
			->method('delete')
			->with($this->equalTo($feed2));

		$this->feedBusinessLayer->purgeDeleted($this->user);
	}


	public function testPurgeDeletedWithoutInterval(){
		$feed1 = new Feed();
		$feed1->setId(3);
		$feed2 = new Feed();
		$feed2->setId(5);
		$feeds = array($feed1, $feed2);

		$this->feedMapper->expects($this->once())
			->method('getToDelete')
			->with($this->equalTo(null), $this->equalTo($this->user))
			->will($this->returnValue($feeds));
		$this->feedMapper->expects($this->at(1))
			->method('delete')
			->with($this->equalTo($feed1));
		$this->feedMapper->expects($this->at(2))
			->method('delete')
			->with($this->equalTo($feed2));

		$this->feedBusinessLayer->purgeDeleted($this->user, false);
	}


	public function testfindAllFromAllUsers() {
		$expected = 'hi';
		$this->feedMapper->expects($this->once())
			->method('findAll')
			->will($this->returnValue($expected));
		$result = $this->feedBusinessLayer->findAllFromAllUsers();
		$this->assertEquals($expected, $result);
	}


}

