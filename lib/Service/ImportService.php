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
use \OCA\News\Service\Exceptions\ServiceValidationException;
use \OCA\News\Utility\HtmlSanitizer;

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
     * @var HtmlSanitizer
     */
    protected $purifier;

    /**
     * FeedService constructor.
     *
     * @param FeedServiceV2   $feedService Service for feeds
     * @param ItemServiceV2   $itemService Service to manage items
     * @param HtmlSanitizer   $purifier    HTML Sanitizer
     * @param LoggerInterface $logger      Logger
     */
    public function __construct(
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        HtmlSanitizer $purifier,
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
     * @return bool Status of the import
     */
    public function articles(string $userId, array $json): bool
    {
        // build assoc array for fast access
        $feeds = $this->feedService->findAllForUser($userId);
        $feedsDict = [];
        foreach ($feeds as $feed) {
            $feedsDict[$feed->getLink()] = $feed;
        }

        $feedLink = "";
        $error = 0;

        // loop over all items and get the corresponding feed
        // if the feed does not exist, create a separate feed for them
        foreach ($json as $entry) {
            try {
                $item = Item::fromImport($entry);
            } catch (\TypeError $e) {
                $error++;
                $this->logger->error(
                    'Invalid data in import entry: ' . $e->getMessage()
                );
                continue;
            }
            $feedLink = $entry['feedLink'];  // this is not set on the item

            if (array_key_exists($feedLink, $feedsDict)) {
                $feed = $feedsDict[$feedLink];
            } else {
                $this->logger->info("Creating new feed for import of {url}", ['url' => $feedLink]);
                $feed = new Feed();
                $feed->setUserId($userId)
                     ->setUrlHash(md5($feedLink))
                     ->setLink($feedLink)
                     ->setUrl($feedLink)
                     ->setTitle('No Title')
                     ->setAdded(time())
                     ->setFolderId(null)
                     ->setPreventUpdate(false);

                /** @var Feed $feed */
                $feed = $this->feedService->insert($feed);
                $feedsDict[$feed->getLink()] = $feed;
            }

            $item->setFeedId($feed->getId())
                 ->setBody($this->purifier->purify($item->getBody()))
                 ->generateSearchIndex();
            if (!$this->itemService->insertOrUpdate($item)) {
                $error++;
            }
        }

        if ($error > 0) {
            throw new ServiceValidationException("Failed to import $error item(s). Please check the server log!");
        }

        return true;
    }
}
