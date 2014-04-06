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

use \OCA\AppFramework\Utility\TimeFactory;
use \OCA\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Item;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\FeedType;


class ItemBusinessLayer extends BusinessLayer {

	private $statusFlag;
	private $autoPurgeCount;
	private $timeFactory;

	public function __construct(ItemMapper $itemMapper, StatusFlag $statusFlag,
								TimeFactory $timeFactory, $autoPurgeCount=0){
		parent::__construct($itemMapper);
		$this->statusFlag = $statusFlag;
		$this->autoPurgeCount = $autoPurgeCount;
		$this->timeFactory = $timeFactory;
	}


	/**
	 * Returns all new items
	 * @param int $id the id of the feed, 0 for starred or all items
	 * @param FeedType $type the type of the feed
	 * @param int $updatedSince a timestamp with the last modification date
	 * returns only items with a >= modified timestamp
	 * @param boolean $showAll if unread items should also be returned
	 * @param string $userId the name of the user
	 * @return array of items
	 */
	public function findAllNew($id, $type, $updatedSince, $showAll, $userId){
		$status = $this->statusFlag->typeToStatus($type, $showAll);

		switch($type){
			case FeedType::FEED:
				$items = $this->mapper->findAllNewFeed($id, $updatedSince,
					                                   $status, $userId);
				break;
			case FeedType::FOLDER:
				$items = $this->mapper->findAllNewFolder($id, $updatedSince,
					                                   $status, $userId);
				break;
			default:
				$items = $this->mapper->findAllNew($updatedSince, $status,
					                               $userId);
		}

		return $items;
	}


	/**
	 * Returns all items
	 * @param int $id the id of the feed, 0 for starred or all items
	 * @param FeedType $type the type of the feed
	 * @param int $limit how many items should be returned
	 * @param int $offset only items lower than this id are returned, 0 for no offset
	 * @param boolean $showAll if unread items should also be returned
	 * @param string $userId the name of the user
	 * @return array of items
	 */
	public function findAll($id, $type, $limit, $offset, $showAll, $userId){
		$status = $this->statusFlag->typeToStatus($type, $showAll);

		switch($type){
			case FeedType::FEED:
				$items = $this->mapper->findAllFeed($id, $limit, $offset,
					                                   $status, $userId);
				break;
			case FeedType::FOLDER:
				$items = $this->mapper->findAllFolder($id, $limit, $offset,
					                                   $status, $userId);
				break;
			default:
				$items = $this->mapper->findAll($limit, $offset, $status,
					                               $userId);
		}

		return $items;
	}


	/**
	 * Star or unstar an item
	 * @param int $feedId the id of the item's feed that should be starred
	 * @param string $guidHash the guidHash of the item that should be starred
	 * @param boolean $isStarred if true the item will be marked as starred, if false unstar
	 * @param $userId the name of the user for security reasons
	 * @throws BusinessLayerException if the item does not exist
	 */
	public function star($feedId, $guidHash, $isStarred, $userId){
		try {
			$item = $this->mapper->findByGuidHash($guidHash, $feedId, $userId);

			$item->setLastModified($this->timeFactory->getTime());
			if($isStarred){
				$item->setStarred();
			} else {
				$item->setUnstarred();
			}
			$this->mapper->update($item);
		} catch(DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}


	/**
	 * Read or unread an item
	 * @param int $itemId the id of the item that should be read
	 * @param boolean $isRead if true the item will be marked as read, if false unread
	 * @param $userId the name of the user for security reasons
	 * @throws BusinessLayerException if the item does not exist
	 */
	public function read($itemId, $isRead, $userId){
		$item = $this->find($itemId, $userId);
		$item->setLastModified($this->timeFactory->getTime());
		if($isRead){
			$item->setRead();
		} else {
			$item->setUnread();
		}
		$this->mapper->update($item);
	}


	/**
	 * Set all items read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasnt seen yet
	 * @param string $userId the name of the user
	 */
	public function readAll($highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->mapper->readAll($highestItemId, $time, $userId);
	}


	/**
	 * Set a folder read
	 * @param int $folderId the id of the folder that should be marked read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasnt seen yet
	 * @param string $userId the name of the user
	 */
	public function readFolder($folderId, $highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->mapper->readFolder($folderId, $highestItemId, $time, $userId);
	}


	/**
	 * Set a feed read
	 * @param int $feedId the id of the feed that should be marked read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasnt seen yet
	 * @param string $userId the name of the user
	 */
	public function readFeed($feedId, $highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->mapper->readFeed($feedId, $highestItemId, $time, $userId);
	}


	/**
	 * This method deletes all unread feeds that are not starred and over the
	 * count of $this->autoPurgeCount starting by the oldest. This is to clean
	 * up the database so that old entries dont spam your db. As criteria for
	 * old, the id is taken
	 */
	public function autoPurgeOld(){
		$this->mapper->deleteReadOlderThanThreshold($this->autoPurgeCount);
	}


	/**
	 * Returns the newest itemd id, use this for marking feeds read
	 * @param string $userId the name of the user
	 * @throws BusinessLayerException if there is no newest item
	 * @return int
	 */
	public function getNewestItemId($userId) {
		try {
			return $this->mapper->getNewestItemId($userId);
		} catch(DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage());
		}
	}


	/**
	 * Returns the starred count
	 * @param string $userId the name of the user
	 * @return int the count
	 */
	public function starredCount($userId){
		return $this->mapper->starredCount($userId);
	}


	/**
	 * @param string $userId from which user the items should be taken
	 * @return array of items which are starred or unread
	 */
	public function getUnreadOrStarred($userId) {
		return $this->mapper->findAllUnreadOrStarred($userId);
	}


	/**
	 * Deletes all items of a user
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$this->mapper->deleteUser($userId);
	}


}
