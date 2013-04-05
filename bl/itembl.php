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

namespace OCA\News\Bl;

use \OCA\News\Db\Item;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\FeedType;


class ItemBl extends Bl {

	private $statusFlag;
	private $autoPurgeCount;

	public function __construct(ItemMapper $itemMapper, StatusFlag $statusFlag,
								$autoPurgeCount=0){
		parent::__construct($itemMapper);
		$this->statusFlag = $statusFlag;
		$this->autoPurgeCount = $autoPurgeCount;
	}


	public function findAllNew($id, $type, $updatedSince, 
		$showAll, $userId){
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


	public function findAll($id, $type, $limit, $offset, 
		$showAll, $userId){
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


	public function starredCount($userId){
		return $this->mapper->starredCount($userId);
	}


	public function star($feedId, $guidHash, $isStarred, $userId){
		// FIXME: this can throw two possible exceptions
		$item = $this->mapper->findByGuidHash($guidHash, $feedId, $userId);
		if($isStarred){
			$item->setStarred();
		} else {
			$item->setUnstarred();
		}
		$this->mapper->update($item);
	}


	public function read($itemId, $isRead, $userId){
		$item = $this->find($itemId, $userId);
		if($isRead){
			$item->setRead();	
		} else {
			$item->setUnread();
		}
		$this->mapper->update($item);
	}


	public function readFeed($feedId, $highestItemId, $userId){
		$this->mapper->readFeed($feedId, $highestItemId, $userId);
	}


	/**
	 * This method deletes all unread feeds that are not starred and over the 
	 * count of $this->autoPurgeCount starting by the oldest. This is to clean
	 * up the database so that old entries dont spam your db. As criteria for
	 * old, the id is taken
	 */
	public function autoPurgeOld(){
		$readAndNotStarred = 
			$this->mapper->getReadOlderThanThreshold($this->autoPurgeCount);
		
		// delete entries with a lower id than last item
		if($this->autoPurgeCount > 0 
			&& isset($readAndNotStarred[$this->autoPurgeCount-1])){
			$this->mapper->deleteReadOlderThanId(
				$readAndNotStarred[$this->autoPurgeCount-1]->getId());
		}
	}


}
