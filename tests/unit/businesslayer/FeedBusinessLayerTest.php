<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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
	private $enhancer;

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
		$this->enhancer = $this->getMockBuilder('\OCA\News\Utility\ArticleEnhancer\Enhancer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = new FeedBusinessLayer($this->feedMapper,
			$this->fetcher, $this->itemMapper, $this->api,
			$timeFactory, $this->autoPurgeMinimumInterval,
			$this->enhancer);
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
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($url))
			->will($this->throwException($ex));
		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->feedBusinessLayer->create($url, 1, $this->user);
	}

	public function testCreate(){
		$url = 'http://test';
		$folderId = 10;
		$createdFeed = new Feed();
		$ex = new DoesNotExistException('yo');
		$createdFeed->setUrl($url);
		$createdFeed->setUrlHash('hsssi');
		$createdFeed->setLink($url);
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
			->with($this->equalTo($createdFeed->getUrlHash()), $this->equalTo($this->user))
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
		$this->enhancer->expects($this->at(0))
			->method('enhance')
			->with($this->equalTo($return[1][1]),
				$this->equalTo($url))
			->will($this->returnValue($return[1][1]));
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
		$this->enhancer->expects($this->at(1))
			->method('enhance')
			->with($this->equalTo($return[1][0]),
				$this->equalTo($url))
			->will($this->returnValue($return[1][0]));
		$this->itemMapper->expects($this->at(3))
			->method('insert')
			->with($this->equalTo($return[1][0]));

		$feed = $this->feedBusinessLayer->create($url, $folderId, $this->user);

		$this->assertEquals($feed->getFolderId(), $folderId);
		$this->assertEquals($feed->getUrl(), $url);
	}


	public function testCreateItemGuidExistsAlready(){
		$url = 'http://test';
		$folderId = 10;
		$ex = new DoesNotExistException('yo');
		$createdFeed = new Feed();
		$createdFeed->setUrl($url);
		$createdFeed->setUrlHash($url);
		$createdFeed->setLink($url);
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
			->with($this->equalTo($createdFeed->getUrlHash()), 
				$this->equalTo($this->user))
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
		$this->enhancer->expects($this->at(0))
			->method('enhance')
			->with($this->equalTo($return[1][1]),
				$this->equalTo($url))
			->will($this->returnValue($return[1][1]));
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
		$feed->setUrlHash('yo');

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
		$this->enhancer->expects($this->at(0))
			->method('enhance')
			->with($this->equalTo($items[0]),
				$this->equalTo($feed->getUrl()))
			->will($this->returnValue($items[0]));
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

	public function testUpdateFails(){
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


	public function testImportArticles(){
		$url = 'http://owncloud/nofeed';

		$feed = new Feed();
		$feed->setId(3);
		$feed->setUserId($this->user);
		$feed->setUrl($url);
		$feed->setLink($url);
		$feed->setTitle('Articles without feed');
		$feed->setAdded($this->time);
		$feed->setFolderId(0);
		$feed->setPreventUpdate(true);

		$feeds = array($feed);

		$item = new Item();
		$item->setFeedId(3);
		$item->setAuthor('john');
		$item->setGuid('s');
		$item->setTitle('hey');
		$item->setPubDate(333);
		$item->setBody('come over');
		$item->setEnclosureMime('mime');
		$item->setEnclosureLink('lin');
		$item->setUnread();
		$item->setUnstarred();
		$item->setLastModified($this->time);

		$json = $item->toExport(array('feed3' => $feed));

		$items = array($json);

		$this->feedMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));

		$this->itemMapper->expects($this->once())
			->method('findByGuidHash')
			->will($this->throwException(new DoesNotExistException('yo')));
		$this->itemMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($item));


		$result = $this->feedBusinessLayer->importArticles($items, $this->user);

		$this->assertEquals(null, $result);
	}


	public function testImportArticlesCreatesOwnFeedWhenNotFound(){
		$url = 'http://owncloud/args';

		$feed = new Feed();
		$feed->setId(3);
		$feed->setUserId($this->user);
		$feed->setUrl($url);
		$feed->setLink($url);
		$feed->setTitle('Articles without feed');
		$feed->setAdded($this->time);
		$feed->setFolderId(0);
		$feed->setPreventUpdate(true);

		$feeds = array($feed);

		$item = new Item();
		$item->setFeedId(3);
		$item->setAuthor('john');
		$item->setGuid('s');
		$item->setTitle('hey');
		$item->setPubDate(333);
		$item->setBody('come over');
		$item->setEnclosureMime('mime');
		$item->setEnclosureLink('lin');
		$item->setUnread();
		$item->setUnstarred();
		$item->setLastModified($this->time);

		$json = $item->toExport(array('feed3' => $feed));
		$json2 = $json;
		$json2['feedLink'] = 'http://test.com'; // believe it or not this copies stuff :D

		$items = array($json, $json2);

		$insertFeed = new Feed();
		$insertFeed->setLink('http://owncloud/nofeed');
		$insertFeed->setUrl('http://owncloud/nofeed');
		$insertFeed->setUserId($this->user);
		$insertFeed->setTitle('Articles without feed');
		$insertFeed->setAdded($this->time);
		$insertFeed->setPreventUpdate(true);
		$insertFeed->setFolderId(0);

		$trans = $this->getMock('trans', array('t'));
		$trans->expects($this->once())
			->method('t')
			->will($this->returnValue('Articles without feed'));
		$this->feedMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($this->user))
			->will($this->returnValue($feeds));
		$this->api->expects($this->once())
			->method('getTrans')
			->will($this->returnValue($trans));
		$this->feedMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($insertFeed))
			->will($this->returnValue($insertFeed));


		$this->itemMapper->expects($this->at(0))
			->method('findByGuidHash')
			->will($this->throwException(new DoesNotExistException('yo')));
		$this->itemMapper->expects($this->at(1))
			->method('insert')
			->with($this->equalTo($item));
			
		$this->itemMapper->expects($this->at(2))
			->method('findByGuidHash')
			->will($this->returnValue($item));
		$this->itemMapper->expects($this->at(3))
			->method('update')
			->with($this->equalTo($item));

		$this->feedMapper->expects($this->once())
			->method('findByUrlHash')
			->will($this->returnValue($feed));

		$result = $this->feedBusinessLayer->importArticles($items, $this->user);

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

