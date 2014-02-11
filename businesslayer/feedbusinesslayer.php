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

use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Utility\TimeFactory;
use \OCA\AppFramework\Core\API;

use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Fetcher\Fetcher;
use \OCA\News\Fetcher\FetcherException;
use \OCA\News\ArticleEnhancer\Enhancer;

class FeedBusinessLayer extends BusinessLayer {

	private $feedFetcher;
	private $itemMapper;
	private $api;
	private $timeFactory;
	private $autoPurgeMinimumInterval;
	private $enhancer;
	private $purifier;

	public function __construct(FeedMapper $feedMapper, Fetcher $feedFetcher,
		                        ItemMapper $itemMapper, API $api,
		                        TimeFactory $timeFactory,
		                        $autoPurgeMinimumInterval,
		                        Enhancer $enhancer,
		                        $purifier){
		parent::__construct($feedMapper);
		$this->feedFetcher = $feedFetcher;
		$this->itemMapper = $itemMapper;
		$this->api = $api;
		$this->timeFactory = $timeFactory;
		$this->autoPurgeMinimumInterval = $autoPurgeMinimumInterval;
		$this->enhancer = $enhancer;
		$this->purifier = $purifier;
	}

	/**
	 * Finds all feeds of a user
	 * @param string $userId the name of the user
	 * @return array of feeds
	 */
	public function findAll($userId){
		return $this->mapper->findAllFromUser($userId);
	}


	/**
	 * Finds all feeds from all users
	 * @return array of feeds
	 */
	public function findAllFromAllUsers() {
		return $this->mapper->findAll();
	}


	/**
	 * Creates a new feed
	 * @param string $feedUrl the url to the feed
	 * @param int $folderId the folder where it should be put into, 0 for root folder
	 * @param string $userId for which user the feed should be created
	 * @throws BusinessLayerConflictException if the feed exists already
	 * @throws BusinessLayerException if the url points to an invalid feed
	 * @return Feed the newly created feed
	 */
	public function create($feedUrl, $folderId, $userId){
		// first try if the feed exists already
		try {
			list($feed, $items) = $this->feedFetcher->fetch($feedUrl);

			// try again if feed exists depending on the reported link
			try {
				$this->mapper->findByUrlHash($feed->getUrlHash(), $userId);
				throw new BusinessLayerConflictException(
					$this->api->getTrans()->t('Can not add feed: Exists already'));
			} catch(DoesNotExistException $ex){}

			// insert feed
			$feed->setFolderId($folderId);
			$feed->setUserId($userId);
			$feed->setArticlesPerUpdate(count($items));
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
					$item = $this->enhancer->enhance($item, $feed->getLink());
					$item->setBody($this->purifier->purify($item->getBody()));
					$this->itemMapper->insert($item);
				}
			}

			// set unread count
			$feed->setUnreadCount($unreadCount);

			return $feed;
		} catch(FetcherException $ex){
			$this->api->log($ex->getMessage(), 'debug');
			throw new BusinessLayerException(
				$this->api->getTrans()->t(
					'Can not add feed: URL does not exist or has invalid xml'));
		}
	}


	/**
	 * Runs all the feed updates
	 */
	public function updateAll(){
		// TODO: this method is not covered by any tests
		$feeds = $this->mapper->findAll();
		foreach($feeds as $feed){
			try {
				$this->update($feed->getId(), $feed->getUserId());
			} catch(BusinessLayerException $ex){
				$this->api->log('Could not update feed ' . $ex->getMessage(),
					'debug');
			}
		}
	}


	/**
	 * Updates a single feed
	 * @param int $feedId the id of the feed that should be updated
	 * @param string $userId the id of the user
	 * @throws BusinessLayerException if the feed does not exist
	 * @return Feed the updated feed entity
	 */
	public function update($feedId, $userId){
		try {
			$existingFeed = $this->mapper->find($feedId, $userId);

			if($existingFeed->getPreventUpdate() === true) {
				return;
			}

			try {
				list($feed, $items) = $this->feedFetcher->fetch(
					$existingFeed->getUrl(), false);

				// update number of articles on every feed update
				if($existingFeed->getArticlesPerUpdate() !== count($items)) {
					$existingFeed->setArticlesPerUpdate(count($items));
					$this->mapper->update($existingFeed);
				}

				// insert items in reverse order because the first one is usually
				// the newest item
				for($i=count($items)-1; $i>=0; $i--){
					$item = $items[$i];
					$item->setFeedId($existingFeed->getId());

					try {
						$this->itemMapper->findByGuidHash($item->getGuidHash(), $feedId, $userId);
					} catch(DoesNotExistException $ex){
						$item = $this->enhancer->enhance($item, 
							$existingFeed->getLink());
						$item->setBody($this->purifier->purify($item->getBody()));
						$this->itemMapper->insert($item);
					}
				}

			} catch(FetcherException $ex){
				// failed updating is not really a problem, so only log it
				$this->api->log('Can not update feed with url ' . $existingFeed->getUrl() .
					': Not found or bad source', 'debug');
				$this->api->log($ex->getMessage(), 'debug');
			}

			return $this->mapper->find($feedId, $userId);

		} catch (DoesNotExistException $ex){
			throw new BusinessLayerException('Feed does not exist');
		}
	}


	/**
	 * Moves a feed into a different folder
	 * @param int $feedId the id of the feed that should be moved
	 * @param int $folderId the id of the folder where the feed should be moved to
	 * @param string $userId the name of the user whose feed should be moved
	 * @throws BusinessLayerException if the feed does not exist
	 */
	public function move($feedId, $folderId, $userId){
		$feed = $this->find($feedId, $userId);
		$feed->setFolderId($folderId);
		$this->mapper->update($feed);
	}


	/**
	 * Rename a feed
	 * @param int $feedId the id of the feed that should be moved
	 * @param string $feedTitle the new title of the feed
	 * @param string $userId the name of the user whose feed should be renamed
	 * @throws BusinessLayerException if the feed does not exist
	 */
	public function rename($feedId, $feedTitle, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setTitle($feedTitle);
		$this->mapper->update($feed);
	}


	/**
	 * Import articles
	 * @param array $json the array with json
	 * @param string userId the username
	 * @return Feed if one had to be created for nonexistent feeds
	 */
	public function importArticles($json, $userId) {
		$url = 'http://owncloud/nofeed';
		$urlHash = md5($url);

		// build assoc array for fast access
		$feeds = $this->findAll($userId);
		$feedsDict = array();
		foreach($feeds as $feed) {
			$feedsDict[$feed->getLink()] = $feed;
		}

		$createdFeed = false;

		// loop over all items and get the corresponding feed
		// if the feed does not exist, create a seperate feed for them
		foreach ($json as $entry) {
			$item = Item::fromImport($entry);
			$item->setLastModified($this->timeFactory->getTime());
			$feedLink = $entry['feedLink'];  // this is not set on the item yet

			if(array_key_exists($feedLink, $feedsDict)) {
				$feed = $feedsDict[$feedLink];
				$item->setFeedId($feed->getId());
			} elseif(array_key_exists($url, $feedsDict)) {
				$feed = $feedsDict[$url];
				$item->setFeedId($feed->getId());				
			} else {
				$createdFeed = true;
				$feed = new Feed();
				$feed->setUserId($userId);
				$feed->setLink($url);
				$feed->setUrl($url);
				$feed->setTitle($this->api->getTrans()->t('Articles without feed'));
				$feed->setAdded($this->timeFactory->getTime());
				$feed->setFolderId(0);
				$feed->setPreventUpdate(true);	
				$feed = $this->mapper->insert($feed);

				$item->setFeedId($feed->getId());
				$feedsDict[$feed->getLink()] = $feed;
			}

			try {
				// if item exists, copy the status
				$existingItem = $this->itemMapper->findByGuidHash(
					$item->getGuidHash(), $feed->getId(), $userId);
				$existingItem->setStatus($item->getStatus());
				$this->itemMapper->update($existingItem);
			} catch(DoesNotExistException $ex){
				$item->setBody($this->purifier->purify($item->getBody()));
				$this->itemMapper->insert($item);
			}
		}

		if($createdFeed) {
			return $this->mapper->findByUrlHash($urlHash, $userId);
		}
	}


	/**
	 * Use this to mark a feed as deleted. That way it can be undeleted
	 * @param int $feedId the id of the feed that should be deleted
	 * @param string $userId the name of the user for security reasons
	 * @throws BusinessLayerException when feed does not exist
	 */
	public function markDeleted($feedId, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setDeletedAt($this->timeFactory->getTime());
		$this->mapper->update($feed);
	}


	/**
	 * Use this to undo a feed deletion
	 * @param int $feedId the id of the feed that should be restored
	 * @param string $userId the name of the user for security reasons
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
