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

namespace OCA\News\Fetcher;

use DateTime;
use Favicon\Favicon;
use FeedIo\Feed\ItemInterface;
use FeedIo\FeedInterface;
use FeedIo\FeedIo;

use Net_URL2;
use OCA\News\Utility\PsrLogger;
use OCP\IL10N;

use OCA\News\Db\Item;
use OCA\News\Db\Feed;
use OCA\News\Utility\Time;
use SimpleXMLElement;

class FeedFetcher implements IFeedFetcher
{

    private $faviconFactory;
    private $reader;
    private $l10n;
    private $time;
    private $logger;

    public function __construct(FeedIo $fetcher, Favicon $favicon, IL10N $l10n, Time $time, PsrLogger $logger)
    {
        $this->reader         = $fetcher;
        $this->faviconFactory = $favicon;
        $this->l10n           = $l10n;
        $this->time           = $time;
        $this->logger         = $logger;
    }


    /**
     * This fetcher handles all the remaining urls therefore always returns true.
     *
     * @param string $url The URL to check
     *
     * @return bool
     */
    public function canHandle($url): bool
    {
        return true;
    }


    /**
     * Fetch a feed from remote
     *
     * @inheritdoc
     */
    public function fetch(string $url, bool $favicon, $lastModified, $user, $password): array
    {
        $url2 = new Net_URL2($url);
        if (!empty($user) && !empty(trim($user))) {
            $url2->setUserinfo(urlencode($user), urlencode($password));
        }
        $url = $url2->getNormalizedURL();
        $this->reader->resetFilters();
        if (empty($lastModified) || !is_string($lastModified)) {
            $resource = $this->reader->read($url);
        } else {
            $resource = $this->reader->readSince($url, new DateTime($lastModified));
        }

        $response = $resource->getResponse();
        if (!$response->isModified()) {
            $this->logger->debug('Feed {url} was not modified since last fetch. old: {old}, new: {new}', [
                 'url' => $url,
                 'old' => print_r($lastModified, true),
                 'new' => print_r($response->getLastModified(), true),
            ]);
            return [null, []];
        }

        $location     = $resource->getUrl();
        $parsedFeed   = $resource->getFeed();
        $feed = $this->buildFeed(
            $parsedFeed,
            $url,
            $favicon,
            $location
        );

        $items = [];
        $this->logger->debug('Feed {url} was modified since last fetch. #{count} items', [
            'url'   => $url,
            'count' => count($parsedFeed),
        ]);
        foreach ($parsedFeed as $item) {
            $items[] = $this->buildItem($item, $parsedFeed);
        }

        return [$feed, $items];
    }

    /**
     * Decode the string twice
     *
     * @param string $string String to decode
     *
     * @return string
     */
    private function decodeTwice($string): string
    {
        return html_entity_decode(
            html_entity_decode(
                $string,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }

    /**
     * Check if a feed is RTL or not
     *
     * @param FeedInterface $parsedFeed The feed that was parsed
     *
     * @return bool
     */
    protected function determineRtl(FeedInterface $parsedFeed): bool
    {
        $language = $parsedFeed->getLanguage();

        $language = strtolower($language);
        $rtl_languages = array(
            'ar', // Arabic (ar-**)
            'fa', // Farsi (fa-**)
            'ur', // Urdu (ur-**)
            'ps', // Pashtu (ps-**)
            'syr', // Syriac (syr-**)
            'dv', // Divehi (dv-**)
            'he', // Hebrew (he-**)
            'yi', // Yiddish (yi-**)
        );
        foreach ($rtl_languages as $prefix) {
            if (strpos($language, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build an item based on a feed.
     *
     * @param ItemInterface $parsedItem The item to use
     * @param FeedInterface $parsedFeed The feed to use
     *
     * @return Item
     */
    protected function buildItem(ItemInterface $parsedItem, FeedInterface $parsedFeed): Item
    {
        $item = new Item();
        $item->setUnread(true);
        $item->setUrl($parsedItem->getLink());
        $item->setGuid($parsedItem->getPublicId());
        $item->setGuidHash(md5($item->getGuid()));

        $lastmodified = $parsedItem->getLastModified() ?? new \DateTime();
        if ($parsedItem->getValue('pubDate') !== null) {
            $pubDT = new DateTime($parsedItem->getValue('pubDate'));
        } elseif ($parsedItem->getValue('published') !== null) {
            $pubDT = new DateTime($parsedItem->getValue('published'));
        } else {
            $pubDT = $lastmodified;
        }

        $item->setPubDate($pubDT->getTimestamp());

        $item->setLastModified($lastmodified->getTimestamp());
        $item->setRtl($this->determineRtl($parsedFeed));

        // unescape content because angularjs helps against XSS
        $item->setTitle($this->decodeTwice($parsedItem->getTitle()));
        $author = $parsedItem->getAuthor();
        if (!is_null($author)) {
            $item->setAuthor($this->decodeTwice($author->getName()));
        }

        // purification is done in the service layer
        $body = $parsedItem->getValue("content:encoded") ?? $parsedItem->getDescription();
        $body = mb_convert_encoding(
            $body,
            'HTML-ENTITIES',
            mb_detect_encoding($body)
        );
        if (strpos($body, 'CDATA') !== false) {
            libxml_use_internal_errors(true);
            $data = simplexml_load_string(
                "<?xml version=\"1.0\"?><item>$body</item>",
                SimpleXMLElement::class,
                LIBXML_NOCDATA
            );
            if ($data !== false && libxml_get_last_error() === false) {
                $body = (string) $data;
            }
            libxml_clear_errors();
        }

        $item->setBody($body);

        if ($parsedItem->hasMedia()) {
            // TODO: Fix multiple media support
            foreach ($parsedItem->getMedias() as $media) {
                if (!$item->isSupportedMime($media->getType())) {
                    continue;
                }
                $item->setEnclosureMime($media->getType());
                $item->setEnclosureLink($media->getUrl());
            }
        }

        $item->generateSearchIndex();

        $this->logger->debug('Added item {title} for feed {feed} publishdate: {datetime}', [
            'title' => $item->getTitle(),
            'feed'  => $parsedFeed->getTitle(),
            'datetime'  => $item->getLastModified(),
        ]);
        return $item;
    }

    /**
     * Build a feed based on provided info
     *
     * @param FeedInterface $feed       Feed to build from
     * @param string        $url        URL to use
     * @param boolean       $getFavicon To get the favicon
     * @param string        $location   String base URL
     *
     * @return Feed
     */
    protected function buildFeed(FeedInterface $feed, string $url, bool $getFavicon, string $location): Feed
    {
        $newFeed = new Feed();

        // unescape content because angularjs helps against XSS
        $title = strip_tags($this->decodeTwice($feed->getTitle()));
        $newFeed->setTitle($title);
        $newFeed->setUrl($url);  // the url used to add the feed
        $newFeed->setLocation($location);  // the url where the feed was found
        $newFeed->setLink($feed->getLink());  // <link> attribute in the feed
        $lastmodified = $feed->getLastModified() ?? new DateTime();
        $newFeed->setLastModified($lastmodified->getTimestamp());
        $newFeed->setAdded($this->time->getTime());

        if (!$getFavicon) {
            return $newFeed;
        }
        $favicon = $this->faviconFactory->get($url);
        $newFeed->setFaviconLink($favicon);

        return $newFeed;
    }
}
