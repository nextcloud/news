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

use HTMLPurifier;

use OCP\ILogger;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Utility\ITimeFactory;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\FetcherException;
use OCA\News\Config\Config;


class FeedService extends Service {

    private $feedFetcher;
    private $itemMapper;
    private $feedMapper;
    private $logger;
    private $l10n;
    private $timeFactory;
    private $autoPurgeMinimumInterval;
    private $purifier;
    private $loggerParams;

    public function __construct(FeedMapper $feedMapper,
                                Fetcher $feedFetcher,
                                ItemMapper $itemMapper,
                                ILogger $logger,
                                IL10N $l10n,
                                ITimeFactory $timeFactory,
                                Config $config,
                                HTMLPurifier $purifier,
                                $LoggerParameters){
        parent::__construct($feedMapper);
        $this->feedFetcher = $feedFetcher;
        $this->itemMapper = $itemMapper;
        $this->logger = $logger;
        $this->l10n = $l10n;
        $this->timeFactory = $timeFactory;
        $this->autoPurgeMinimumInterval =
            $config->getAutoPurgeMinimumInterval();
        $this->purifier = $purifier;
        $this->feedMapper = $feedMapper;
        $this->loggerParams = $LoggerParameters;
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
     * @param int $folderId the folder where it should be put into, 0 for root
     * folder
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
            $itemCount = count($items);
            $feed->setFolderId($folderId);
            $feed->setUserId($userId);
            $feed->setArticlesPerUpdate($itemCount);

            if ($title !== null && $title !== '') {
                $feed->setTitle($title);
            }

            $feed = $this->feedMapper->insert($feed);

            // insert items in reverse order because the first one is usually
            // the newest item
            $unreadCount = 0;
            for($i=$itemCount-1; $i>=0; $i--){
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
                    $item->setBody($this->purifier->purify($item->getBody()));
                    $this->itemMapper->insert($item);
                }
            }

            // set unread count
            $feed->setUnreadCount($unreadCount);

            return $feed;
        } catch(FetcherException $ex){
            $this->logger->debug($ex->getMessage(), $this->loggerParams);
            throw new ServiceNotFoundException($ex->getMessage());
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
                $this->logger->debug(
                    'Could not update feed ' . $ex->getMessage(),
                    $this->loggerParams
                );
            }
        }
    }


    /**
     * Updates a single feed
     * @param int $feedId the id of the feed that should be updated
     * @param string $userId the id of the user
     * @param bool $forceUpdate update even if the article exists already
     * @throws ServiceNotFoundException if the feed does not exist
     * @return Feed the updated feed entity
     */
    public function update($feedId, $userId, $forceUpdate=false){
        $existingFeed = $this->find($feedId, $userId);

        if($existingFeed->getPreventUpdate() === true) {
            return $existingFeed;
        }

        // for backwards compability it can be that the location is not set
        // yet, if so use the url
        $location = $existingFeed->getLocation();
        if (!$location) {
            $location = $existingFeed->getUrl();
        }

        try {
            list($fetchedFeed, $items) = $this->feedFetcher->fetch(
                $location,
                false,
                $existingFeed->getLastModified(),
                $existingFeed->getEtag(),
                $existingFeed->getFullTextEnabled()
            );

            // if there is no feed it means that no update took place
            if (!$fetchedFeed) {
                return $existingFeed;
            }

            // update number of articles on every feed update
            $itemCount = count($items);

            // this is needed to adjust to updates that add more items
            // than when the feed was created. You can't update the count
            // if it's lower because it may be due to the caching headers
            // that were sent as the request and it might cause unwanted
            // deletion and reappearing of feeds
            if ($itemCount > $existingFeed->getArticlesPerUpdate()) {
                $existingFeed->setArticlesPerUpdate($itemCount);
            }

            $existingFeed->setLastModified($fetchedFeed->getLastModified());
            $existingFeed->setEtag($fetchedFeed->getEtag());
            $existingFeed->setLocation($fetchedFeed->getLocation());
            $this->feedMapper->update($existingFeed);

            // insert items in reverse order because the first one is
            // usually the newest item
            for($i=$itemCount-1; $i>=0; $i--){
                $item = $items[$i];
                $item->setFeedId($existingFeed->getId());

                try {
                    $dbItem = $this->itemMapper->findByGuidHash(
                        $item->getGuidHash(), $feedId, $userId
                    );

                    // in case of update
                    if ($forceUpdate ||
                        $item->getPubDate() > $dbItem->getPubDate()) {

                        $dbItem->setTitle($item->getTitle());
                        $dbItem->setUrl($item->getUrl());
                        $dbItem->setAuthor($item->getAuthor());
                        $dbItem->setSearchIndex($item->getSearchIndex());
                        $dbItem->setRtl($item->getRtl());
                        $dbItem->setLastModified($item->getLastModified());
                        $dbItem->setPubDate($item->getPubDate());
                        $dbItem->setEnclosureMime($item->getEnclosureMime());
                        $dbItem->setEnclosureLink($item->getEnclosureLink());
                        $dbItem->setBody(
                            $this->purifier->purify($item->getBody())
                        );

                        // update modes: 0 nothing, 1 set unread
                        if ($existingFeed->getUpdateMode() === 1) {
                            $dbItem->setUnread();
                        }

                        $this->itemMapper->update($dbItem);
                    }
                } catch(DoesNotExistException $ex){
                    $item->setBody(
                        $this->purifier->purify($item->getBody())
                    );
                    $this->itemMapper->insert($item);
                }
            }

        } catch(FetcherException $ex){
            // failed updating is not really a problem, so only log it
            $this->logger->debug(
                'Can not update feed with url ' . $existingFeed->getUrl() .
                ' and location ' . $existingFeed->getLocation() .
                ': ' . $ex->getMessage(),
                $this->loggerParams
            );
        }

        return $this->find($feedId, $userId);
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
                $item->generateSearchIndex();
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

    /**
     * @param $feedId
     * @param $userId
     * @param $diff an array containing the fields to update, e.g.:
     *  [
     *      'ordering' => 1,
     *      'fullTextEnabled' => true,
     *      'pinned' => true,
     *      'updateMode' => 0,
     *      'title' => 'title'
     * ]
     * @throws ServiceNotFoundException if feed does not exist
     */
    public function patch($feedId, $userId, $diff=[]) {
        $feed = $this->find($feedId, $userId);

        // these attributes just map onto the feed object without extra logic
        $simplePatches = ['ordering', 'pinned', 'updateMode', 'title', 'folderId'];
        foreach ($simplePatches as $attribute) {
            if (array_key_exists($attribute, $diff)) {
                $method = 'set' . ucfirst($attribute);
                $feed->$method($diff[$attribute]);
            }
        }

        // special feed updates
        if (array_key_exists('fullTextEnabled', $diff)) {
            // disable caching for the next update
            $feed->setEtag('');
            $feed->setLastModified(0);
            $feed->setFullTextEnabled($diff['fullTextEnabled']);
            $this->feedMapper->update($feed);
            return $this->update($feedId, $userId, true);
        }

        return $this->feedMapper->update($feed);
    }

}
