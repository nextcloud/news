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

namespace OCA\News\BusinessLayer;

require_once(__DIR__ . "/../../classloader.php");

use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Item;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\FeedType;


class ItemBusinessLayerTest extends \PHPUnit_Framework_TestCase {

	private $mapper;
	private $itemBusinessLayer;
	private $user;
	private $response;
	private $status;
	private $time;
	private $newestItemId;


	protected function setUp(){
		$this->time = 222;
		$timeFactory = $this->getMock('TimeFactory', ['getTime']);
		$timeFactory->expects($this->any())
			->method('getTime')
			->will($this->returnValue($this->time));
		$this->mapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
			->disableOriginalConstructor()
			->getMock();
		$statusFlag = $this->getMockBuilder('\OCA\News\Db\StatusFlag')
			->disableOriginalConstructor()
			->getMock();
		$this->status = StatusFlag::STARRED;
		$statusFlag->expects($this->any())
			->method('typeToStatus')
			->will($this->returnValue($this->status));
		$this->threshold = 2;
		$config = $this->getMockBuilder(
			'\OCA\News\Utility\Config')
			->disableOriginalConstructor()
			->getMock();
		$config->expects($this->any())
			->method('getAutoPurgeCount')
			->will($this->returnValue($this->threshold));
		$this->itemBusinessLayer = new ItemBusinessLayer($this->mapper,
			$statusFlag, $timeFactory, $config);
		$this->user = 'jack';
		$this->id = 3;
		$this->updatedSince = 20333;
		$this->showAll = true;
		$this->offset = 5;
		$this->limit = 20;
		$this->newestItemId = 4;
	}


	public function testFindAllNewFeed(){
		$type = FeedType::FEED;
		$this->mapper->expects($this->once())
			->method('findAllNewFeed')
			->with($this->equalTo($this->id),
					$this->equalTo($this->updatedSince),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAllNew(
			$this->id, $type, $this->updatedSince, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllNewFolder(){
		$type = FeedType::FOLDER;
		$this->mapper->expects($this->once())
			->method('findAllNewFolder')
			->with($this->equalTo($this->id),
					$this->equalTo($this->updatedSince),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAllNew(
			$this->id, $type, $this->updatedSince, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllNew(){
		$type = FeedType::STARRED;
		$this->mapper->expects($this->once())
			->method('findAllNew')
			->with(	$this->equalTo($this->updatedSince),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAllNew(
			$this->id, $type, $this->updatedSince, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllFeed(){
		$type = FeedType::FEED;
		$this->mapper->expects($this->once())
			->method('findAllFeed')
			->with($this->equalTo($this->id),
					$this->equalTo($this->limit),
					$this->equalTo($this->offset),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAll(
			$this->id, $type, $this->limit,
			$this->offset, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testFindAllFolder(){
		$type = FeedType::FOLDER;
		$this->mapper->expects($this->once())
			->method('findAllFolder')
			->with($this->equalTo($this->id),
					$this->equalTo($this->limit),
					$this->equalTo($this->offset),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAll(
			$this->id, $type, $this->limit,
			$this->offset, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testFindAll(){
		$type = FeedType::STARRED;
		$this->mapper->expects($this->once())
			->method('findAll')
			->with(	$this->equalTo($this->limit),
					$this->equalTo($this->offset),
					$this->equalTo($this->status),
					$this->equalTo($this->user))
			->will($this->returnValue($this->response));

		$result = $this->itemBusinessLayer->findAll(
			$this->id, $type, $this->limit,
			$this->offset, $this->showAll,
			$this->user);
		$this->assertEquals($this->response, $result);
	}


	public function testStar(){
		$itemId = 3;
		$feedId = 5;
		$guidHash = md5('hihi');

		$item = new Item();
		$item->setStatus(128);
		$item->setId($itemId);
		$item->setUnstarred();

		$expectedItem = new Item();
		$expectedItem->setStatus(128);
		$expectedItem->setStarred();
		$expectedItem->setId($itemId);
		$expectedItem->setLastModified($this->time);

		$this->mapper->expects($this->once())
			->method('findByGuidHash')
			->with(
				$this->equalTo($guidHash),
				$this->equalTo($feedId),
				$this->equalTo($this->user))
			->will($this->returnValue($item));

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($expectedItem));

		$this->itemBusinessLayer->star($feedId, $guidHash, true, $this->user);

		$this->assertTrue($item->isStarred());
	}


	public function testRead(){
		$itemId = 3;
		$item = new Item();
		$item->setStatus(128);
		$item->setId($itemId);
		$item->setRead();

		$expectedItem = new Item();
		$expectedItem->setStatus(128);
		$expectedItem->setUnread();
		$expectedItem->setId($itemId);
		$expectedItem->setLastModified($this->time);

		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo($itemId), $this->equalTo($this->user))
			->will($this->returnValue($item));

		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($expectedItem));

		$this->itemBusinessLayer->read($itemId, false, $this->user);

		$this->assertTrue($item->isUnread());
	}


	public function testStarDoesNotExist(){

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->mapper->expects($this->once())
			->method('findByGuidHash')
			->will($this->throwException(new DoesNotExistException('')));

		$this->itemBusinessLayer->star(1, 'hash', true, $this->user);
	}


	public function testReadAll(){
		$highestItemId = 6;

		$this->mapper->expects($this->once())
			->method('readAll')
			->with($this->equalTo($highestItemId),
				$this->equalTo($this->time),
				$this->equalTo($this->user));

		$this->itemBusinessLayer->readAll($highestItemId, $this->user);
	}


	public function testReadFolder(){
		$folderId = 3;
		$highestItemId = 6;

		$this->mapper->expects($this->once())
			->method('readFolder')
			->with($this->equalTo($folderId),
				$this->equalTo($highestItemId),
				$this->equalTo($this->time),
				$this->equalTo($this->user));

		$this->itemBusinessLayer->readFolder($folderId, $highestItemId, $this->user);
	}


	public function testReadFeed(){
		$feedId = 3;
		$highestItemId = 6;

		$this->mapper->expects($this->once())
			->method('readFeed')
			->with($this->equalTo($feedId),
				$this->equalTo($highestItemId),
				$this->equalTo($this->time),
				$this->equalTo($this->user));

		$this->itemBusinessLayer->readFeed($feedId, $highestItemId, $this->user);
	}


	public function testAutoPurgeOldWillPurgeOld(){
		$this->mapper->expects($this->once())
			->method('deleteReadOlderThanThreshold')
			->with($this->equalTo($this->threshold));

		$this->itemBusinessLayer->autoPurgeOld();
	}


	public function testGetNewestItemId() {
		$this->mapper->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->returnValue(12));

		$result = $this->itemBusinessLayer->getNewestItemId($this->user);
		$this->assertEquals(12, $result);
	}


	public function testGetNewestItemIdDoesNotExist() {
		$this->mapper->expects($this->once())
			->method('getNewestItemId')
			->with($this->equalTo($this->user))
			->will($this->throwException(new DoesNotExistException('There are no items')));

		$this->setExpectedException('\OCA\News\BusinessLayer\BusinessLayerException');
		$this->itemBusinessLayer->getNewestItemId($this->user);
	}


	public function testStarredCount(){
		$star = 18;

		$this->mapper->expects($this->once())
			->method('starredCount')
			->with($this->equalTo($this->user))
			->will($this->returnValue($star));

		$result = $this->itemBusinessLayer->starredCount($this->user);

		$this->assertEquals($star, $result);
	}


	public function testGetUnreadOrStarred(){
		$star = 18;

		$this->mapper->expects($this->once())
			->method('findAllUnreadOrStarred')
			->with($this->equalTo($this->user))
			->will($this->returnValue($star));

		$result = $this->itemBusinessLayer->getUnreadOrStarred($this->user);

		$this->assertEquals($star, $result);
	}


	public function testDeleteUser() {
		$this->mapper->expects($this->once())
			->method('deleteUser')
			->will($this->returnValue($this->user));

		$this->itemBusinessLayer->deleteUser($this->user);
	}



}


