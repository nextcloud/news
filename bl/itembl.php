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


class ItemBl extends Bl {

	public function __construct(ItemMapper $itemMapper){
		parent::__construct($itemMapper);
	}


	public function findAllNew($id, $type, $updatedSince, $userId){
		// TODO all the crazy finding of items
	}


	public function findAll($id, $type, $limit, $offset, $userId){
		// TODO all the crazy finding of items
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


	public function readFeed($feedId, $userId){
		$this->mapper->readFeed($feedId, $userId);
	}


	// ATTENTION: this does no validation and is only for creating
	// items from the fetcher
	public function create($item){
		$this->mapper->insert($item);
	}

}
