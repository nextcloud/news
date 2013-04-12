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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Core\API;

use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Utility\Fetcher;
use \OCA\News\Utility\FetcherException;

class FeedBl extends Bl {

	private $feedFetcher;
	private $itemMapper;
	private $api;

	public function __construct(FeedMapper $feedMapper, Fetcher $feedFetcher,
		                        ItemMapper $itemMapper, API $api){
		parent::__construct($feedMapper);
		$this->feedFetcher = $feedFetcher;
		$this->itemMapper = $itemMapper;
		$this->api = $api;
	}


	public function findAll($userId){
		return $this->mapper->findAllFromUser($userId);
	}


	public function create($feedUrl, $folderId, $userId){
		// first try if the feed exists already
		try {
			$this->mapper->findByUrlHash(md5($feedUrl), $userId);
			throw new BLException(
				$this->api->getTrans()->t('Can not add feed: Exists already'));
		} catch(DoesNotExistException $ex){}
		
		try {
			list($feed, $items) = $this->feedFetcher->fetch($feedUrl);
			
			// insert feed
			$feed->setFolderId($folderId);
			$feed->setUserId($userId);
			$feed = $this->mapper->insert($feed);

			// insert items in reverse order because the first one is usually the
			// newest item
			for($i=count($items)-1; $i>=0; $i--){
				$item = $items[$i];
				$item->setFeedId($feed->getId());
				$this->itemMapper->insert($item);
			}

			// set unread count
			$feed->setUnreadCount(count($items));
			
			return $feed;
		} catch(FetcherException $ex){
			$this->api->log($ex->getMessage());
			throw new BLException(
				$this->api->getTrans()->t(
					'Can not add feed: URL does not exist or has invalid xml'));
		}
	}


	// FIXME: this method is not covered by any tests
	public function updateAll(){
		$feeds = $this->mapper->findAll();
		foreach($feeds as $feed){
			try {
				$this->update($feed->getId(), $feed->getUserId());
			} catch(BLException $ex){
				continue;
			}
		}
	}


	public function update($feedId, $userId){
		$existingFeed = $this->mapper->find($feedId, $userId);
		try {
			list($feed, $items) = $this->feedFetcher->fetch($existingFeed->getUrl());

			// insert items in reverse order because the first one is usually the
			// newest item
			for($i=count($items)-1; $i>=0; $i--){
				$item = $items[$i];
				$item->setFeedId($existingFeed->getId());

				// if a doesnotexist exception is being thrown the entry does not 
				// exist and the item needs to be created, otherwise
				// update it
				try {
					$existing = $this->itemMapper->findByGuidHash(
						$item->getGuidHash(), $feedId, $userId);

					// in case of an update the existing item has to be deleted
					// if the pub_date changed because we sort by id on the 
					// client side since this is the only reliable way to do it
					// to not get weird behaviour
					if($existing->getPubDate() !== $item->getPubDate()){

						// because the item is being replaced we need to keep 
						// status flags but we want the new entry to be unread
						$item->setStatus($existing->getStatus());
						$item->setUnread();

						$this->itemMapper->delete($existing);
						$this->itemMapper->insert($item);
					}

				} catch(DoesNotExistException $ex){
					$this->itemMapper->insert($item);
				}
			}

		} catch(FetcherException $ex){
			// failed updating is not really a problem, so only log it
			$this->api->log('Can not update feed with url' . $existingFeed->getUrl() .
				': Not found or bad source');
		}
		
		return $this->mapper->find($feedId, $userId);
	}


	public function move($feedId, $folderId, $userId){
		$feed = $this->find($feedId, $userId);
		$feed->setFolderId($folderId);
		$this->mapper->update($feed);
	}


}
