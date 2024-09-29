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
use FeedIo\Reader\ReadErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use Net_URL2;
use OCP\IL10N;
use OCP\ITempManager;

use OCA\News\Db\Item;
use OCA\News\Db\Feed;
use OCA\News\Utility\Time;
use OCA\News\Utility\Cache;
use OCA\News\Scraper\Scraper;
use OCA\News\Config\FetcherConfig;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class FeedFetcher implements IFeedFetcher
{

    /**
     * @var Favicon
     */
    private $faviconFactory;

    /**
     * @var FeedIo
     */
    private $reader;

    /**
     * @var Scraper
     */
    private $scraper;

    /**
     * @var IL10N
     */
    private $l10n;

    /**
     * @var Time
     */
    private $time;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FetcherConfig
     */
    private $fetcherConfig;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(
        FeedIo $fetcher,
        Favicon $favicon,
        Scraper $scraper,
        IL10N $l10n,
        Time $time,
        LoggerInterface $logger,
        FetcherConfig $fetcherConfig,
        Cache $cache
    ) {
        $this->reader         = $fetcher;
        $this->faviconFactory = $favicon;
        $this->scraper        = $scraper;
        $this->l10n           = $l10n;
        $this->time           = $time;
        $this->logger         = $logger;
        $this->fetcherConfig  = $fetcherConfig;
        $this->cache          = $cache;
    }


    /**
     * This fetcher handles all the remaining urls therefore always returns true.
     *
     * @param string $url The URL to check
     *
     * @return bool
     */
    public function canHandle(string $url): bool
    {
        return true;
    }


    /**
     * Fetch a feed from remote
     *
     * @inheritdoc
     */
    public function fetch(
        string $url,
        bool $fullTextEnabled,
        ?string $user,
        ?string $password,
        ?string $httpLastModified
    ): array {
        $url2 = new Net_URL2($url);
        if (!is_null($user) && trim($user) !== '') {
            $url2->setUserinfo(rawurlencode($user), rawurlencode($password));
        }
        if (!is_null($httpLastModified) && trim($httpLastModified) !== '') {
            $lastModified = new DateTime($httpLastModified);
        } else {
            $lastModified = null;
        }
        $url = $url2->getNormalizedURL();
        $resource = $this->reader->read($url, null, $lastModified);

        $location     = $resource->getUrl();
        $parsedFeed   = $resource->getFeed();
        $feed = $this->buildFeed(
            $parsedFeed,
            $url,
            $location
        );

        if (!is_null($resource->getResponse()->getLastModified())) {
            $feed->setHttpLastModified($resource->getResponse()->getLastModified()->format(DateTime::RSS));
        } elseif (!is_null($lastModified)) {
            $feed->setHttpLastModified($lastModified->format(DateTime::RSS));
        }

        $items = [];
        $RTL = $this->determineRtl($parsedFeed);
        $feedName = $parsedFeed->getTitle();
        $feedAuthor = $parsedFeed->getAuthor();
        $this->logger->debug(
            'Feed {url} was modified since last fetch. #{count} items',
            [
            'url'   => $url,
            'count' => count($parsedFeed),
            ]
        );

        foreach ($parsedFeed as $item) {
            $body = null;
            $currRTL = $RTL;

            // Scrape the content if full-text is enabled and if the feed provides a URL
            if ($fullTextEnabled) {
                $itemLink = $item->getLink();
                if ($itemLink !== null && $this->scraper->scrape($itemLink)) {
                    $body = $this->scraper->getContent();
                    $currRTL = $this->scraper->getRTL($currRTL);
                }
            }

            $builtItem = $this->buildItem($item, $body, $currRTL, $feedAuthor);
            $this->logger->debug(
                'Added item {title} for feed {feed} lastmodified: {datetime}',
                [
                'title' => $builtItem->getTitle(),
                'feed'  => $feedName,
                'datetime'  => $builtItem->getLastModified(),
                ]
            );
            $items[] = $builtItem;
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
    private function decodeTwice(string $string): string
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
     * @param string|null   $body       Text of the item, if not provided use description from $parsedItem
     * @param bool          $RTL        True if the feed is RTL (Right-to-left)
     * @param string|null   $feedAuthor Author of the feed as fallback when the item has no Author
     *
     * @return Item
     */
    protected function buildItem(
        ItemInterface $parsedItem,
        ?string $body = null,
        bool $RTL = false,
        $feedAuthor = null
    ): Item {
        $item = new Item();
        $item->setUnread(true);
        $itemLink = $parsedItem->getLink();
        $itemTitle = $parsedItem->getTitle();
        $item->setUrl($itemLink);
        $publicId = $parsedItem->getPublicId();
        if ($publicId == null) {
            // Fallback on using the URL as the guid for the feed item if no guid provided by feed
            $this->logger->debug(
                "Feed item {title} with link {link} did not expose a guid, falling back to using link as guid",
                [
                'title' => $itemTitle,
                'link' => $itemLink
                ]
            );
            $publicId = $itemLink;
        }
        if ($publicId == null) {
            throw new ReadErrorException("Malformed feed: item has no GUID");
        }
        $item->setGuid($publicId);
        $item->setGuidHash(md5($item->getGuid()));

        $lastModified = $parsedItem->getLastModified() ?? new DateTime();
        if ($parsedItem->getValue('pubDate') !== null) {
            $pubDT = new DateTime($parsedItem->getValue('pubDate'));
        } elseif ($parsedItem->getValue('published') !== null) {
            $pubDT = new DateTime($parsedItem->getValue('published'));
        } else {
            $pubDT = $lastModified;
        }

        $item->setPubDate($pubDT->getTimestamp());

        $item->setLastModified($lastModified->getTimestamp());
        $item->setRtl($RTL);

        // unescape content because angularjs helps against XSS
        if ($itemTitle !== null) {
            $item->setTitle($this->decodeTwice($itemTitle));
        }

        $author = $parsedItem->getAuthor() ?? $feedAuthor;

        if ($author !== null && $author->getName() !== null) {
            $item->setAuthor($this->decodeTwice($author->getName()));
        }

        $categories = [];
        foreach ($parsedItem->getCategories() as $category) {
            if ($category->getLabel() !== null) {
                $categories[] = $this->decodeTwice($category->getLabel());
            }
        }
        $item->setCategories($categories);

        // Use description from feed if body is not provided (by a scraper)
        if ($body === null) {
            $body = $parsedItem->getValue('content:encoded')
                    ?? $parsedItem->getContent()
                    ?? $parsedItem->getSummary();
        }

        // purification is done in the service layer
        if (!is_null($body)) {
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
        }

        $item->setBody($body);

        if ($parsedItem->hasMedia()) {
            // TODO: Fix multiple media support
            foreach ($parsedItem->getMedias() as $media) {
                if (!$item->isSupportedMime($media->getType())
                    && !$media->getThumbnail()
                    && !$media->getDescription()
                ) {
                    continue;
                }
                $item->setEnclosureMime($media->getType());
                $item->setEnclosureLink($media->getUrl());
                $item->setMediaThumbnail($media->getThumbnail());
                if ($media->getDescription()) {
                    $description = str_replace("\n", "<br>", $media->getDescription());
                    $item->setMediaDescription($description);
                }
            }
        }

        $item->generateSearchIndex();
        return $item;
    }

    /**
     * Return the favicon for a given feed and url
     *
     * @param FeedInterface $feed Feed to check for a logo
     * @param string        $url  Original URL for the feed
     *
     * @return string|null
     */
    protected function getFavicon(FeedInterface $feed, string $url): ?string
    {
        $favicon = null;
        // trim the string because authors do funny things
        $feed_logo = $feed->getLogo();

        if (!is_null($feed_logo)) {
            $favicon = trim($feed_logo);
        }

        ini_set('user_agent', $this->fetcherConfig->getUserAgent());

        $base_url = new Net_URL2($url);
        $base_url->setPath("");
        $base_url = $base_url->getNormalizedURL();

        // Return if the URL is empty
        if ($base_url === null || trim($base_url) === '') {
            return null;
        }

        // check if feed has a logo entry
        if ($favicon === null || $favicon === '') {
            $return = $this->faviconFactory->get($base_url);
            return is_string($return) ? $return : null;
        }

        $logo_cache = $this->cache->getCache("feedLogo");

        // file name of the logo is md5 of the url
        $favicon_path = join(DIRECTORY_SEPARATOR, [$logo_cache, md5($favicon)]);
        $downloaded = false;

        if (file_exists($favicon_path)) {
            $last_modified = filemtime($favicon_path);
        } else {
            $last_modified = 0;
        }

        try {
            // Base_uri can only be set on creation, will be used when link is relative.
            $client = new Client(['base_uri' => $base_url]);
            $response = $client->request(
                'GET',
                $favicon,
                [
                    'sink' => $favicon_path,
                    'headers' => [
                        'User-Agent'        => FetcherConfig::DEFAULT_USER_AGENT,
                        'Accept'            => 'image/*',
                        'If-Modified-Since' => date(DateTime::RFC7231, $last_modified),
                        'Accept-Encoding'   => $this->fetcherConfig->checkEncoding()
                    ]
                ]
            );
            $downloaded = true;

            $this->logger->debug(
                "Feed:{feed} Logo:{logo} Status:{status}",
                [
                'status' => $response->getStatusCode(),
                'feed'   => $url,
                'logo'   => $favicon
                ]
            );
        } catch (RequestException | ConnectException $e) {
            $this->logger->info(
                'An error occurred while trying to download the feed logo of {url}: {error}',
                [
                'url'   => $url,
                'error' => $e->getMessage() ?? 'Unknown'
                ]
            );
        }

        $is_image = $downloaded && substr(mime_content_type($favicon_path), 0, 5) === "image";

        // check if file is actually an image
        if (!$is_image) {
            $this->logger->debug(
                "Downloaded file:{file} from {url} is not an image",
                [
                'file' => $favicon_path,
                'url'   => $favicon
                ]
            );

            $return = $this->faviconFactory->get($base_url);
            return is_string($return) ? $return : null;
        }

        list($width, $height, $type, $attr) = getimagesize($favicon_path);
        // check if image is square else fall back to favicon
        if ($width !== $height) {
            $this->logger->debug(
                "Downloaded file:{file} from {url} is not square",
                [
                'file' => $favicon_path,
                'url'   => $favicon
                ]
            );
            $return = $this->faviconFactory->get($base_url);
            return is_string($return) ? $return : null;
        }

        return is_string($favicon) ? $favicon : null;
    }

    /**
     * Build a feed based on provided info
     *
     * @param FeedInterface $feed     Feed to build from
     * @param string        $url      URL to use
     * @param string        $location String base URL
     *
     * @return Feed
     */
    protected function buildFeed(FeedInterface $feed, string $url, string $location): Feed
    {
        $newFeed = new Feed();

        // unescape content because angularjs helps against XSS
        if ($feed->getTitle() !== null) {
            $title = strip_tags($this->decodeTwice($feed->getTitle()));
            $newFeed->setTitle($title);
        }

        $newFeed->setUrl($url);  // the url used to add the feed
        $newFeed->setLocation($location);  // the url where the feed was found
        $newFeed->setLink($feed->getLink());  // <link> attribute in the feed
        $newFeed->setAdded($this->time->getTime());

        $favicon = $this->getFavicon($feed, $url);
        $newFeed->setFaviconLink($favicon);

        return $newFeed;
    }
}
