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

namespace OCA\News\Service;

use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\ItemMapper;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\FeedType;
use \OCA\News\Utility\Config;


class ItemService extends Service {

	private $statusFlag;
	private $autoPurgeCount;
	private $timeFactory;
	private $itemMapper;

	public function __construct(ItemMapper $itemMapper,
	                            StatusFlag $statusFlag,
								$timeFactory,
	                            Config $config){
		parent::__construct($itemMapper);
		$this->statusFlag = $statusFlag;
		$this->autoPurgeCount = $config->getAutoPurgeCount();
		$this->timeFactory = $timeFactory;
		$this->itemMapper = $itemMapper;
	}


	/**
	 * Returns all new items
	 * @param int $id the id of the feed, 0 for starred or all items
	 * @param int $type the type of the feed
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
				return $this->itemMapper->findAllNewFeed(
					$id, $updatedSince, $status, $userId
				);
			case FeedType::FOLDER:
				return $this->itemMapper->findAllNewFolder(
					$id, $updatedSince, $status, $userId
				);
			default:
				return $this->itemMapper->findAllNew(
					$updatedSince, $status, $userId
				);
		}
	}


	/**
	 * Returns all items
	 * @param int $id the id of the feed, 0 for starred or all items
	 * @param int $type the type of the feed
	 * @param int $limit how many items should be returned
	 * @param int $offset the offset
	 * @param boolean $showAll if unread items should also be returned
	 * @param boolean $oldestFirst if it should be ordered by oldest first
	 * @param string $userId the name of the user
	 * @return array of items
	 */
	public function findAll($id, $type, $limit, $offset, $showAll, $oldestFirst,
	                        $userId){
		$status = $this->statusFlag->typeToStatus($type, $showAll);

		switch($type){
			case FeedType::FEED:
				return $this->itemMapper->findAllFeed(
					$id, $limit, $offset, $status, $oldestFirst, $userId
				);
			case FeedType::FOLDER:
				return $this->itemMapper->findAllFolder(
					$id, $limit, $offset, $status, $oldestFirst, $userId
				);
			default:
				return $this->itemMapper->findAll(
					$limit, $offset, $status, $oldestFirst, $userId
				);
		}
	}


	/**
	 * Star or unstar an item
	 * @param int $feedId the id of the item's feed that should be starred
	 * @param string $guidHash the guidHash of the item that should be starred
	 * @param boolean $isStarred if true the item will be marked as starred, if false unstar
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException if the item does not exist
	 */
	public function star($feedId, $guidHash, $isStarred, $userId){
		try {
			$item = $this->itemMapper->findByGuidHash($guidHash, $feedId, $userId);

			$item->setLastModified($this->timeFactory->getTime());
			if($isStarred){
				$item->setStarred();
			} else {
				$item->setUnstarred();
			}
			$this->itemMapper->update($item);
		} catch(DoesNotExistException $ex) {
			throw new ServiceNotFoundException($ex->getMessage());
		}
	}


	/**
	 * Read or unread an item
	 * @param int $itemId the id of the item that should be read
	 * @param boolean $isRead if true the item will be marked as read, if false unread
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException if the item does not exist
	 */
	public function read($itemId, $isRead, $userId){
		$item = $this->find($itemId, $userId);
		$item->setLastModified($this->timeFactory->getTime());
		if($isRead){
			$item->setRead();
		} else {
			$item->setUnread();
		}
		$this->itemMapper->update($item);
	}


	/**
	 * Set all items read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasn't seen yet
	 * @param string $userId the name of the user
	 */
	public function readAll($highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->itemMapper->readAll($highestItemId, $time, $userId);
	}


	/**
	 * Set a folder read
	 * @param int $folderId the id of the folder that should be marked read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasn't seen yet
	 * @param string $userId the name of the user
	 */
	public function readFolder($folderId, $highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->itemMapper->readFolder($folderId, $highestItemId, $time, $userId);
	}


	/**
	 * Set a feed read
	 * @param int $feedId the id of the feed that should be marked read
	 * @param int $highestItemId all items below that are marked read. This is used
	 * to prevent marking items as read that the users hasn't seen yet
	 * @param string $userId the name of the user
	 */
	public function readFeed($feedId, $highestItemId, $userId){
		$time = $this->timeFactory->getTime();
		$this->itemMapper->readFeed($feedId, $highestItemId, $time, $userId);
	}


	/**
	 * This method deletes all unread feeds that are not starred and over the
	 * count of $this->autoPurgeCount starting by the oldest. This is to clean
	 * up the database so that old entries don't spam your db. As criteria for
	 * old, the id is taken
	 */
	public function autoPurgeOld(){
		$this->itemMapper->deleteReadOlderThanThreshold($this->autoPurgeCount);
	}


	/**
	 * Returns the newest item id, use this for marking feeds read
	 * @param string $userId the name of the user
	 * @throws ServiceNotFoundException if there is no newest item
	 * @return int
	 */
	public function getNewestItemId($userId) {
		try {
			return $this->itemMapper->getNewestItemId($userId);
		} catch(DoesNotExistException $ex) {
			throw new ServiceNotFoundException($ex->getMessage());
		}
	}


	/**
	 * Returns the starred count
	 * @param string $userId the name of the user
	 * @return int the count
	 */
	public function starredCount($userId){
		return $this->itemMapper->starredCount($userId);
	}


	/**
	 * @param string $userId from which user the items should be taken
	 * @return array of items which are starred or unread
	 */
	public function getUnreadOrStarred($userId) {
		return $this->itemMapper->findAllUnreadOrStarred($userId);
	}


	/**
	 * Deletes all items of a user
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$this->itemMapper->deleteUser($userId);
	}


}
