<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Service;

use FeedIo\Explorer;
use FeedIo\Reader\ReadErrorException;
use HTMLPurifier;

use OCA\News\Db\FeedMapperV2;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\ILogger;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCA\News\Db\FeedMapper;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Config\Config;
use OCA\News\Utility\Time;
use Psr\Log\LoggerInterface;

/**
 * Class FeedService
 *
 * @package OCA\News\Service
 */
class FeedServiceV2 extends Service
{
    /**
     * Class to fetch feeds.
     * @var FeedFetcher
     */
    protected $feedFetcher;
    /**
     * Items service.
     *
     * @var ItemServiceV2
     */
    protected $itemService;
    /**
     * HTML Purifier
     * @var HTMLPurifier
     */
    protected $purifier;
    /**
     * Feed Explorer
     * @var Explorer
     */
    protected $explorer;

    /**
     * FeedService constructor.
     *
     * @param FeedMapperV2    $mapper      DB layer for feeds
     * @param FeedFetcher     $feedFetcher FeedIO interface
     * @param ItemServiceV2   $itemService Service to manage items
     * @param Explorer        $explorer    Feed Explorer
     * @param HTMLPurifier    $purifier    HTML Purifier
     * @param LoggerInterface $logger      Logger
     */
    public function __construct(
        FeedMapperV2 $mapper,
        FeedFetcher $feedFetcher,
        ItemServiceV2 $itemService,
        Explorer $explorer,
        HTMLPurifier $purifier,
        LoggerInterface $logger
    ) {
        parent::__construct($mapper, $logger);

        $this->feedFetcher = $feedFetcher;
        $this->itemService = $itemService;
        $this->explorer    = $explorer;
        $this->purifier    = $purifier;
    }

    /**
     * Finds all feeds of a user
     *
     * @param string $userId the name/ID of the user
     * @param array  $params Filter parameters
     *
     * @return Feed[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }

    /**
     * Finds a feed of a user
     *
     * @param string $userId the name of the user
     * @param string $id     the id of the feed
     *
     * @return Feed
     *
     * @throws DoesNotExistException
     * @throws MultipleObjectsReturnedException
     */
    public function findForUser(string $userId, string $id): Feed
    {
        return $this->mapper->findFromUser($userId, $id);
    }

    /**
     * @param int|null $id
     *
     * @return Feed[]
     */
    public function findAllFromFolder(?int $id): array
    {
        return $this->mapper->findAllFromFolder($id);
    }

    /**
     * Finds all feeds of a user and all items in it
     *
     * @param  string $userId the name of the user
     *
     * @return Feed[]
     */
    public function findAllForUserRecursive(string $userId): array
    {
        $feeds = $this->mapper->findAllFromUser($userId);

        foreach ($feeds as &$feed) {
            $items = $this->itemService->findAllForFeed($feed->getId());
            $feed->items = $items;
        }
        return $feeds;
    }

    /**
     * Finds all feeds
     *
     * @return Feed[]
     */
    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    /**
     * Check if a feed exists for a user
     *
     * @param string $userID the name of the user
     * @param string $url    the feed URL
     *
     * @return bool
     */
    public function existsForUser(string $userID, string $url): bool
    {
        try {
            $this->mapper->findByURL($userID, $url);
            return true;
        } catch (DoesNotExistException $e) {
            return false;
        }
    }


    /**
     * Creates a new feed
     *
     * @param string      $userId    Feed owner
     * @param string      $feedUrl   Feed URL
     * @param int|null    $folderId  Target folder, defaults to root
     * @param bool        $full_text Scrape the feed for full text
     * @param string|null $title     The feed title
     * @param string|null $user      Basic auth username, if set
     * @param string|null $password  Basic auth password if username is set
     *
     * @return Feed|Entity
     *
     * @throws ServiceConflictException The feed already exists
     * @throws ServiceNotFoundException The url points to an invalid feed
     */
    public function create(
        string $userId,
        string $feedUrl,
        ?int $folderId = null,
        bool $full_text = false,
        ?string $title = null,
        ?string $user = null,
        ?string $password = null
    ): Entity {
        if ($this->existsForUser($userId, $feedUrl)) {
            throw new ServiceConflictException('Feed with this URL exists');
        }

        $feeds = $this->explorer->discover($feedUrl);
        if ($feeds !== []) {
            $feedUrl = array_shift($feeds);
        }

        try {
            /**
             * @var Feed   $feed
             * @var Item[] $items
             */
            list($feed, $items) = $this->feedFetcher->fetch($feedUrl, true, '0', $full_text, $user, $password);
        } catch (ReadErrorException $ex) {
            $this->logger->debug($ex->getMessage());
            throw new ServiceNotFoundException($ex->getMessage());
        }

        if ($feed === null) {
            throw new ServiceNotFoundException('Failed to fetch feed');
        }

        $feed->setFolderId($folderId)
            ->setUserId($userId)
            ->setHttpLastModified(null)
            ->setArticlesPerUpdate(count($items));

        if (!is_null($title)) {
            $feed->setTitle($title);
        }

        if (!is_null($user)) {
            $feed->setBasicAuthUser($user)
                 ->setBasicAuthPassword($password);
        }

        return $this->mapper->insert($feed);
    }


    /**
     * Update a feed
     *
     * @param Feed|Entity $feed Feed item
     *
     * @return Feed|Entity Database feed entity
     */
    public function fetch(Entity $feed): Entity
    {
        if ($feed->getPreventUpdate() === true) {
            return $feed;
        }

        // for backwards compability it can be that the location is not set
        // yet, if so use the url
        $location = $feed->getLocation() ?? $feed->getUrl();

        try {
            /**
             * @var Feed   $feed
             * @var Item[] $items
             */
            list($fetchedFeed, $items) = $this->feedFetcher->fetch(
                $location,
                false,
                $feed->getHttpLastModified(),
                $feed->getFullTextEnabled(),
                $feed->getBasicAuthUser(),
                $feed->getBasicAuthPassword()
            );

            // if there is no feed it means that no update took place
            if (!$fetchedFeed) {
                return $feed;
            }

            // update number of articles on every feed update
            $itemCount = count($items);

            // this is needed to adjust to updates that add more items
            // than when the feed was created. You can't update the count
            // if it's lower because it may be due to the caching headers
            // that were sent as the request and it might cause unwanted
            // deletion and reappearing of feeds
            if ($itemCount > $feed->getArticlesPerUpdate()) {
                $feed->setArticlesPerUpdate($itemCount);
            }

            $feed->setHttpLastModified($fetchedFeed->getHttpLastModified())
                 ->setHttpEtag($fetchedFeed->getHttpEtag())
                 ->setLocation($fetchedFeed->getLocation());

            // insert items in reverse order because the first one is
            // usually the newest item
            for ($i = $itemCount - 1; $i >= 0; $i--) {
                $item = $items[$i];
                $item->setFeedId($feed->getId())
                     ->setBody($this->purifier->purify($item->getBody()));

                // update modes: 0 nothing, 1 set unread
                if ($feed->getUpdateMode() === 1) {
                    $item->setUnread(true);
                }

                $this->itemService->insertOrUpdate($item);
            }

            // mark feed as successfully updated
            $feed->setUpdateErrorCount(0);
            $feed->setLastUpdateError(null);
        } catch (ReadErrorException $ex) {
            $feed->setUpdateErrorCount($feed->getUpdateErrorCount() + 1);
            $feed->setLastUpdateError($ex->getMessage());
        }

        return $this->mapper->update($feed);
    }

    public function delete(string $user, int $id): void
    {
        $feed = $this->mapper->findFromUser($user, $id);
        $this->mapper->delete($feed);
    }

    /**
     * Remove deleted entities.
     *
     * @param string|null $userID       The user to purge
     * @param int|null    $minTimestamp The timestamp to purge from
     *
     * @return void
     */
    public function purgeDeleted(?string $userID, ?int $minTimestamp): void
    {
        $this->mapper->purgeDeleted($userID, $minTimestamp);
    }

    public function fetchAll(): void
    {
        foreach ($this->findAll() as $feed) {
            $this->fetch($feed);
        }
    }
}
