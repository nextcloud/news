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

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;

use \Psr\Log\LoggerInterface;
use \HTMLPurifier;

/**
 * Class ImportService
 *
 * @package OCA\News\Service
 */
class ImportService
{
    /**
     * Items service.
     *
     * @var ItemServiceV2
     */
    protected $itemService;
    /**
     * Feeds service.
     *
     * @var FeedServiceV2
     */
    protected $feedService;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var HTMLPurifier
     */
    protected $purifier;

    /**
     * FeedService constructor.
     *
     * @param FeedServiceV2   $feedService Service for feeds
     * @param ItemServiceV2   $itemService Service to manage items
     * @param HTMLPurifier    $purifier    HTML Purifier
     * @param LoggerInterface $logger      Logger
     */
    public function __construct(
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        HTMLPurifier $purifier,
        LoggerInterface $logger
    ) {
        $this->itemService = $itemService;
        $this->feedService = $feedService;
        $this->purifier    = $purifier;
        $this->logger      = $logger;
    }

    /**
     * @param string $userId
     * @param array  $json
     *
     * @return \OCP\AppFramework\Db\Entity|null
     */
    public function importArticles(string $userId, array $json): ?\OCP\AppFramework\Db\Entity
    {
        $url = 'http://nextcloud/nofeed';

        // build assoc array for fast access
        $feeds = $this->feedService->findAllForUser($userId);
        $feedsDict = [];
        foreach ($feeds as $feed) {
            $feedsDict[$feed->getLink()] = $feed;
        }

        $createdFeed = false;

        // loop over all items and get the corresponding feed
        // if the feed does not exist, create a separate feed for them
        foreach ($json as $entry) {
            $item = Item::fromImport($entry);
            $feedLink = $entry['feedLink'];  // this is not set on the item yet

            if (array_key_exists($feedLink, $feedsDict)) {
                $feed = $feedsDict[$feedLink];
            } else {
                $createdFeed = true;
                $feed = new Feed();
                $feed->setUserId($userId)
                     ->setUrlHash(md5($url))
                     ->setLink($url)
                     ->setUrl($url)
                     ->setTitle('Articles without feed')
                     ->setAdded(time())
                     ->setFolderId(null)
                     ->setPreventUpdate(true);

                /** @var Feed $feed */
                $feed = $this->feedService->insert($feed);
                $feedsDict[$feed->getLink()] = $feed;
            }

            $item->setFeedId($feed->getId())
                 ->setBody($this->purifier->purify($item->getBody()))
                 ->generateSearchIndex();
            $this->itemService->insertOrUpdate($item);
        }

        if (!$createdFeed) {
            return null;
        }

        return $this->feedService->findByURL($userId, $url);
    }
}
