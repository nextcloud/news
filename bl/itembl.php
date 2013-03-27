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

	public function __construct(ItemMapper $itemMapper, StatusFlag $statusFlag){
		parent::__construct($itemMapper);
		$this->statusFlag = $statusFlag;
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


	public function star($itemId, $isStarred, $userId){
		$item = $this->find($itemId, $userId);
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


}
