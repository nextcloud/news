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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Utility\TimeFactory;
use \OCA\AppFramework\Core\API;

use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Utility\Fetcher;
use \OCA\News\Utility\FetcherException;
use \OCA\News\Utility\ImportParser;

class FeedBusinessLayer extends BusinessLayer {

	private $feedFetcher;
	private $itemMapper;
	private $api;
	private $timeFactory;
	private $importParser;
	private $autoPurgeMinimumInterval;

	public function __construct(FeedMapper $feedMapper, Fetcher $feedFetcher,
		                        ItemMapper $itemMapper, API $api,
		                        TimeFactory $timeFactory,
		                        ImportParser $importParser,
		                        $autoPurgeMinimumInterval){
		parent::__construct($feedMapper);
		$this->feedFetcher = $feedFetcher;
		$this->itemMapper = $itemMapper;
		$this->api = $api;
		$this->timeFactory = $timeFactory;
		$this->importParser = $importParser;
		$this->autoPurgeMinimumInterval = $autoPurgeMinimumInterval;
	}


	public function findAll($userId){
		return $this->mapper->findAllFromUser($userId);
	}


	/**
	 * @throws BusinessLayerExistsException if the feed exists already
	 * @throws BusinessLayerException if the url points to an invalid feed
	 */
	public function create($feedUrl, $folderId, $userId){
		// first try if the feed exists already
		try {
			$this->mapper->findByUrlHash(md5($feedUrl), $userId);
			throw new BusinessLayerExistsException(
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
			$unreadCount = 0;
			for($i=count($items)-1; $i>=0; $i--){
				$item = $items[$i];
				$item->setFeedId($feed->getId());

				// check if item exists (guidhash is the same)
				// and ignore it if it does
				try {
					$this->itemMapper->findByGuidHash(
						$item->getGuidHash(), $item->getFeedId(), $userId);
					continue;
				} catch(DoesNotExistException $ex){
					$unreadCount += 1;
					$this->itemMapper->insert($item);
				}
			}

			// set unread count
			$feed->setUnreadCount($unreadCount);
			
			return $feed;
		} catch(FetcherException $ex){
			$this->api->log($ex->getMessage());
			throw new BusinessLayerException(
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
			} catch(BusinessLayerException $ex){
				$this->api->log('Could not update feed ' . $ex->getMessage());
			}
		}
	}


	/**
	 * @throws BusinessLayerException if the feed does not exist
	 */
	public function update($feedId, $userId){
		try {
			$existingFeed = $this->mapper->find($feedId, $userId);

			if($existingFeed->getPreventUpdate() === true) {
				return;
			}

			try {
				list($feed, $items) = $this->feedFetcher->fetch(
					$existingFeed->getUrl());

				// insert items in reverse order because the first one is usually 
				// the newest item
				for($i=count($items)-1; $i>=0; $i--){
					$item = $items[$i];
					$item->setFeedId($existingFeed->getId());

					try {
						$existing = $this->itemMapper->findByGuidHash(
							$item->getGuidHash(), $feedId, $userId);

						// in case of an update the existing item has to be deleted
						// if the pub_date changed because we sort by id on the 
						// client side since this is the only reliable way to do it
						// to not get weird behaviour
						if((int)$existing->getPubDate() !== (int)$item->getPubDate()){

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
				$this->api->log($ex->getMessage());
			}
			
			return $this->mapper->find($feedId, $userId);
		
		} catch (DoesNotExistException $ex){
			throw new BusinessLayerException('Feed does not exist');
		}
	}


	/**
	 * @throws BusinessLayerException if the feed does not exist
	 */
	public function move($feedId, $folderId, $userId){
		$feed = $this->find($feedId, $userId);
		$feed->setFolderId($folderId);
		$this->mapper->update($feed);
	}


	/**
	 * Imports the google reader json
	 * @param array $json the array with json
	 * @param string userId the username
	 * @return Feed the created feed
	 */
	public function importGoogleReaderJSON($json, $userId) {
		$url = 'http://owncloud/googlereader';
		$urlHash = md5($url);

		try {
			$feed = $this->mapper->findByUrlHash($urlHash, $userId);
		} catch(DoesNotExistException $ex) {
			$feed = new Feed();
			$feed->setUserId($userId);
			$feed->setUrlHash($urlHash);
			$feed->setUrl($url);
			$feed->setTitle('Google Reader');
			$feed->setAdded($this->timeFactory->getTime());
			$feed->setFolderId(0);
			$feed->setPreventUpdate(true);
			$feed = $this->mapper->insert($feed);
		}

		foreach($this->importParser->parse($json) as $item) {
			$item->setFeedId($feed->getId());
			try {
				$this->itemMapper->findByGuidHash(
					$item->getGuidHash(), $item->getFeedId(), $userId);
			} catch(DoesNotExistException $ex) {
				$this->itemMapper->insert($item);
			}
		}

		return $this->mapper->findByUrlHash($urlHash, $userId);

	}


	/**
	 * Use this to mark a feed as deleted. That way it can be undeleted
	 * @throws BusinessLayerException when feed does not exist
	 */
	public function markDeleted($feedId, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setDeletedAt($this->timeFactory->getTime());
		$this->mapper->update($feed);
	}


	/**
	 * Use this to undo a feed deletion
	 * @throws BusinessLayerException when feed does not exist
	 */
	public function unmarkDeleted($feedId, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setDeletedAt(0);
		$this->mapper->update($feed);
	}


	/**
	 * Deletes all deleted feeds
	 * @param string $userId if given it purges only feeds of that user
	 * @param boolean $useInterval defaults to true, if true it only purges
	 * entries in a given interval to give the user a chance to undo the 
	 * deletion
	 */
	public function purgeDeleted($userId=null, $useInterval=true) {
		$deleteOlderThan = null;

		if ($useInterval) {
			$now = $this->timeFactory->getTime();
			$deleteOlderThan = $now - $this->autoPurgeMinimumInterval;
		}

		$toDelete = $this->mapper->getToDelete($deleteOlderThan, $userId);	

		foreach ($toDelete as $feed) {
			$this->mapper->delete($feed);
		}
	}


}
