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

use \OCP\ILogger;
use \OCP\IL10N;
use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Fetcher\Fetcher;
use \OCA\News\Fetcher\FetcherException;
use \OCA\News\ArticleEnhancer\Enhancer;
use \OCA\News\Utility\Config;


class FeedService extends Service {

	private $feedFetcher;
	private $itemMapper;
	private $feedMapper;
	private $logger;
	private $l10n;
	private $timeFactory;
	private $autoPurgeMinimumInterval;
	private $enhancer;
	private $purifier;
	private $loggerParams;

	public function __construct(FeedMapper $feedMapper,
	                            Fetcher $feedFetcher,
		                        ItemMapper $itemMapper,
		                        ILogger $logger,
		                        IL10N $l10n,
		                        $timeFactory,
		                        Config $config,
		                        Enhancer $enhancer,
		                        $purifier,
		                        $loggerParams){
		parent::__construct($feedMapper);
		$this->feedFetcher = $feedFetcher;
		$this->itemMapper = $itemMapper;
		$this->logger = $logger;
		$this->l10n = $l10n;
		$this->timeFactory = $timeFactory;
		$this->autoPurgeMinimumInterval = $config->getAutoPurgeMinimumInterval();
		$this->enhancer = $enhancer;
		$this->purifier = $purifier;
		$this->feedMapper = $feedMapper;
		$this->loggerParams = $loggerParams;
	}

	/**
	 * Finds all feeds of a user
	 * @param string $userId the name of the user
	 * @return Feed[]
	 */
	public function findAll($userId){
		return $this->feedMapper->findAllFromUser($userId);
	}


	/**
	 * Finds all feeds from all users
	 * @return array of feeds
	 */
	public function findAllFromAllUsers() {
		return $this->feedMapper->findAll();
	}


	/**
	 * Creates a new feed
	 * @param string $feedUrl the url to the feed
	 * @param int $folderId the folder where it should be put into, 0 for root folder
	 * @param string $userId for which user the feed should be created
	 * @param string $title if given, this is used for the opml feed title
	 * @throws ServiceConflictException if the feed exists already
	 * @throws ServiceNotFoundException if the url points to an invalid feed
	 * @return Feed the newly created feed
	 */
	public function create($feedUrl, $folderId, $userId, $title=null){
		// first try if the feed exists already
		try {
			list($feed, $items) = $this->feedFetcher->fetch($feedUrl);

			// try again if feed exists depending on the reported link
			try {
				$this->feedMapper->findByUrlHash($feed->getUrlHash(), $userId);
				throw new ServiceConflictException(
					$this->l10n->t('Can not add feed: Exists already'));

			// If no matching feed was found everything was ok
			} catch(DoesNotExistException $ex){}

			// insert feed
			$feed->setFolderId($folderId);
			$feed->setUserId($userId);
			$feed->setArticlesPerUpdate(count($items));

			if ($title) {
				$feed->setTitle($title);
			}

			$feed = $this->feedMapper->insert($feed);

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
			$this->logger->debug($ex->getMessage(), $this->loggerParams);
			throw new ServiceNotFoundException(
				$this->l10n->t(
					'Can not add feed: URL does not exist, SSL Certificate can not be validated ' .
					'or feed has invalid xml'));
		}
	}


	/**
	 * Runs all the feed updates
	 */
	public function updateAll(){
		// TODO: this method is not covered by any tests
		$feeds = $this->feedMapper->findAll();
		foreach($feeds as $feed){
			try {
				$this->update($feed->getId(), $feed->getUserId());
			} catch(\Exception $ex){
				$this->logger->debug('Could not update feed ' . $ex->getMessage(),
					$this->loggerParams);
			}
		}
	}


	/**
	 * Updates a single feed
	 * @param int $feedId the id of the feed that should be updated
	 * @param string $userId the id of the user
	 * @throws ServiceNotFoundException if the feed does not exist
	 * @return Feed the updated feed entity
	 */
	public function update($feedId, $userId){
		try {
			$existingFeed = $this->feedMapper->find($feedId, $userId);

			if($existingFeed->getPreventUpdate() === true) {
				return $existingFeed;
			}

			try {
				list(, $items) = $this->feedFetcher->fetch(
					$existingFeed->getUrl(), false);

				// update number of articles on every feed update
				if($existingFeed->getArticlesPerUpdate() !== count($items)) {
					$existingFeed->setArticlesPerUpdate(count($items));
					$this->feedMapper->update($existingFeed);
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

				$this->logger->debug('Can not update feed with url ' . $existingFeed->getUrl() .
					': Not found or bad source', $this->loggerParams);
				$this->logger->debug($ex->getMessage(), $this->loggerParams);
			}

			return $this->feedMapper->find($feedId, $userId);

		} catch (DoesNotExistException $ex){
			throw new ServiceNotFoundException('Feed does not exist');
		}

	}


	/**
	 * Moves a feed into a different folder
	 * @param int $feedId the id of the feed that should be moved
	 * @param int $folderId the id of the folder where the feed should be moved to
	 * @param string $userId the name of the user whose feed should be moved
	 * @throws ServiceNotFoundException if the feed does not exist
	 */
	public function move($feedId, $folderId, $userId){
		$feed = $this->find($feedId, $userId);
		$feed->setFolderId($folderId);
		$this->feedMapper->update($feed);
	}


	/**
	 * Rename a feed
	 * @param int $feedId the id of the feed that should be moved
	 * @param string $feedTitle the new title of the feed
	 * @param string $userId the name of the user whose feed should be renamed
	 * @throws ServiceNotFoundException if the feed does not exist
	 */
	public function rename($feedId, $feedTitle, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setTitle($feedTitle);
		$this->feedMapper->update($feed);
	}


	/**
	 * Import articles
	 * @param array $json the array with json
	 * @param string $userId the username
	 * @return Feed if one had to be created for nonexistent feeds
	 */
	public function importArticles($json, $userId) {
		$url = 'http://owncloud/nofeed';
		$urlHash = md5($url);

		// build assoc array for fast access
		$feeds = $this->findAll($userId);
		$feedsDict = [];
		foreach($feeds as $feed) {
			$feedsDict[$feed->getLink()] = $feed;
		}

		$createdFeed = false;

		// loop over all items and get the corresponding feed
		// if the feed does not exist, create a separate feed for them
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
				$feed->setTitle($this->l10n->t('Articles without feed'));
				$feed->setAdded($this->timeFactory->getTime());
				$feed->setFolderId(0);
				$feed->setPreventUpdate(true);
				$feed = $this->feedMapper->insert($feed);

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
			return $this->feedMapper->findByUrlHash($urlHash, $userId);
		}

        return null;
	}


	/**
	 * Use this to mark a feed as deleted. That way it can be un-deleted
	 * @param int $feedId the id of the feed that should be deleted
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException when feed does not exist
	 */
	public function markDeleted($feedId, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setDeletedAt($this->timeFactory->getTime());
		$this->feedMapper->update($feed);
	}


	/**
	 * Use this to undo a feed deletion
	 * @param int $feedId the id of the feed that should be restored
	 * @param string $userId the name of the user for security reasons
	 * @throws ServiceNotFoundException when feed does not exist
	 */
	public function unmarkDeleted($feedId, $userId) {
		$feed = $this->find($feedId, $userId);
		$feed->setDeletedAt(0);
		$this->feedMapper->update($feed);
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

		$toDelete = $this->feedMapper->getToDelete($deleteOlderThan, $userId);

		foreach ($toDelete as $feed) {
			$this->feedMapper->delete($feed);
		}
	}


	/**
	 * Deletes all feeds of a user, delete items first since the user_id
	 * is not defined in there
	 * @param string $userId the name of the user
	 */
	public function deleteUser($userId) {
		$this->feedMapper->deleteUser($userId);
	}


}
