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
use OCA\News\Vendor\Favicon\Favicon;
use OCA\News\Vendor\FeedIo\Feed\ItemInterface;
use OCA\News\Vendor\FeedIo\FeedInterface;
use OCA\News\Vendor\FeedIo\FeedIo;
use OCA\News\Vendor\FeedIo\Reader\ReadErrorException;
use OCA\News\Vendor\GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

use OCP\IL10N;

use OCA\News\Constants;
use OCA\News\Db\Item;
use OCA\News\Db\Feed;
use OCA\News\Utility\Time;
use OCA\News\Utility\Cache;
use OCA\News\Utility\AppData;
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

    /**
     * @var AppData
     */
    private $appData;

    public function __construct(
        FeedIo $fetcher,
        Favicon $favicon,
        Scraper $scraper,
        IL10N $l10n,
        Time $time,
        LoggerInterface $logger,
        FetcherConfig $fetcherConfig,
        Cache $cache,
        AppData $appData
    ) {
        $this->reader         = $fetcher;
        $this->faviconFactory = $favicon;
        $this->scraper        = $scraper;
        $this->l10n           = $l10n;
        $this->time           = $time;
        $this->logger         = $logger;
        $this->fetcherConfig  = $fetcherConfig;
        $this->cache          = $cache;
        $this->appData        = $appData;
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
     * Check if the feed server send the Last-Modified header
     *
     * @param string $url The URL to check
     *
     * @return bool
     */
    public function hasLastModifiedHeader(string $url): bool
    {
        $hasLastModified = false;
        $httpClientConfig = [
            'base_uri' => $url,
            'timeout' => 3,
        ];
        try {
            $client = $this->fetcherConfig->getHttpClient($httpClientConfig);
            $response = $client->request('HEAD');
            $hasLastModified = $response->hasHeader('Last-Modified');
        } catch (\Exception) {
            $this->logger->warning('Check for Last-Modified header failed for ' . $url);
        }
        return $hasLastModified;
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
        $url2 = new Uri($url);
        if (!is_null($user) && trim($user) !== '') {
            $url2 = $url2->withUserInfo(rawurlencode($user), rawurlencode($password));
        }
        if (!is_null($httpLastModified) && trim($httpLastModified) !== '') {
            try {
                $lastModified = new DateTime($httpLastModified);
            } catch (\Exception) {
                $lastModified = null;
            }
        } else {
            $lastModified = null;
        }
        $url = (string) $url2;
        $resource = $this->reader->read($url, null, $lastModified);

        $location     = $resource->getUrl();
        $parsedFeed   = $resource->getFeed();
        $feed = $this->buildFeed(
            $parsedFeed,
            $url,
            $location
        );

        // Set the next calculated update time, but maximum 24 hours from now
        $feed->setNextUpdateTime(nextUpdateTime: min($resource->getNextUpdate(
            sleepyDuration: $this->fetcherConfig::SLEEPY_DURATION
        )?->getTimestamp(), time() + 86400));

        $this->logger->debug(
            'Feed {url} was parsed and nextUpdateTime is {nextUpdateTime}',
            [
            'url'   => $url,
            'nextUpdateTime' => $feed->getNextUpdateTime()
            ]
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
            'Feed {url} was modified since last fetch. #{count} items, nextUpdateTime is {nextUpdateTime}',
            [
            'url'   => $url,
            'count' => count($parsedFeed),
            'nextUpdateTime' => $feed->getNextUpdateTime(),
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
        try {
            if ($parsedItem->getValue('pubDate') !== null) {
                $pubDT = new DateTime($parsedItem->getValue('pubDate'));
            } elseif ($parsedItem->getValue('published') !== null) {
                $pubDT = new DateTime($parsedItem->getValue('published'));
            } else {
                $pubDT = $lastModified;
            }
        } catch (\Exception) {
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
            // First, handle CDATA sections if present
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
            
            if (!mb_check_encoding($body, 'UTF-8')) {
                // Convert to UTF-8 if needed with comprehensive encoding detection
                $encodingList = ['ISO-8859-1', 'Windows-1252', 'ASCII', 'UTF-16', 'UTF-16BE', 'UTF-16LE'];
                $detectedEncoding = mb_detect_encoding($body, $encodingList, true);
                if ($detectedEncoding !== false) {
                    $convertedBody = mb_convert_encoding($body, 'UTF-8', $detectedEncoding);
                    if ($convertedBody !== false) {
                        $body = $convertedBody;
                    } else {
                        $this->logger->warning(
                            'Failed to convert encoding from {from} to UTF-8 for feed item',
                            ['from' => $detectedEncoding]
                        );
                    }
                }
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
        ini_set('user_agent', $this->fetcherConfig->getUserAgent());

        $base_url = new Uri($url);
        $base_url = (string) $base_url->withPath('/');

        // Return if the URL is empty
        if ($base_url === null || trim($base_url) === '') {
            return null;
        }

        // Step 1: Check if current feed logo still exists
        $feed_logo = $this->appData->getFileContent(Constants::LOGO_INFO_DIR, 'url_'.md5($url));
        if (!is_null($feed_logo)) {
            $favicon = trim($feed_logo);
            if ($favicon !== '') {
                $logo_result = $this->downloadFavicon($favicon, $base_url, $url, true);
                if ($logo_result !== null) {
                    return $logo_result;
                }
            }
        }

        // Step 2: Check if feed has a logo entry and try to use it
        $feed_logo = $feed->getLogo();
        if (!is_null($feed_logo)) {
            $favicon = trim($feed_logo);
            if ($favicon !== '') {
                $logo_result = $this->downloadFavicon($favicon, $base_url, $url, false);
                if ($logo_result !== null) {
                    return $logo_result;
                }
            }
        }

        // Step 3: Try to get favicon from the feed URL
        $feed_favicon = $this->faviconFactory->get($base_url);
        if (is_string($feed_favicon) && $feed_favicon !== '') {
            $this->logger->debug(
                "Found favicon from feed URL: {favicon} for feed: {feed}",
                [
                'favicon' => $feed_favicon,
                'feed' => $url
                ]
            );
            $logo_result = $this->downloadFavicon($feed_favicon, $base_url, $url, false);
            if ($logo_result !== null) {
                return $logo_result;
            }
        }

        // Step 4: Try to get favicon from the feed's link element (website URL)
        $feed_link = $feed->getLink();
        if (!is_null($feed_link) && trim($feed_link) !== '') {
            try {
                $link_uri = new Uri($feed_link);
                $link_base_url = (string) $link_uri->withPath('/');
                
                if ($link_base_url !== $base_url) { // Only try if it's different from feed URL
                    $link_favicon = $this->faviconFactory->get($link_base_url);
                    if (is_string($link_favicon) && $link_favicon !== '') {
                        $this->logger->debug(
                            "Found favicon from feed link: {favicon} for feed: {feed}",
                            [
                            'favicon' => $link_favicon,
                            'feed' => $url
                            ]
                        );
                        $logo_result = $this->downloadFavicon($link_favicon, $base_url, $url, false);
                        if ($logo_result !== null) {
                                return $logo_result;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->debug(
                    'Could not parse feed link URL {link} for feed {feed}: {error}',
                    [
                    'link' => $feed_link,
                    'feed' => $url,
                    'error' => $e->getMessage()
                    ]
                );
            }
        }

        return null;
    }

    /**
     * Try to download and validate a favicon from a given URL
     *
     * @param string $favicon_url The favicon URL to download
     * @param string $base_url    The base URL for relative URLs
     * @param string $feed_url    The original feed URL for logging
     * @param bool   $use_mtime   Use if-modified-since to check for changes
     *
     * @return string|null The favicon URL if valid and modified, null otherwise
     */
    protected function downloadFavicon(
        string $favicon_url,
        string $base_url,
        string $feed_url,
        bool   $use_mtime
    ): ?string {

        $logo_cache = $this->cache->getCache("feedLogo");

        // file name of the cached logo is md5 of the favicon url
        $favicon_url_hash = md5($favicon_url);
        $favicon_cache = join(DIRECTORY_SEPARATOR, [$logo_cache, $favicon_url_hash]);

        // use mtime from stored logo when looking for changes in step 1
        if ($use_mtime) {
            $last_modified = $this->appData->getMTime(Constants::LOGO_IMAGE_DIR, $favicon_url_hash) ?? 0;
        } else {
            $last_modified = 0;
        }

        // Base_uri can only be set on creation, will be used when link is relative.
        $httpClientConfig = [
            'base_uri' => $base_url,
            'timeout' => 10,
        ];
        try {
            $client = $this->fetcherConfig->getHttpClient($httpClientConfig);
            $response = $client->request(
                'GET',
                $favicon_url,
                [
                    'sink' => $favicon_cache,
                    'headers' => [
                        'Accept'            => 'image/*',
                        'If-Modified-Since' => date(DateTime::RFC7231, $last_modified),
                        'Accept-Encoding'   => $this->fetcherConfig->checkEncoding()
                    ]
                ]
            );

            $this->logger->debug(
                "Feed:{feed} Logo:{logo} Status:{status}",
                [
                'status' => $response->getStatusCode(),
                'feed'   => $feed_url,
                'logo'   => $favicon_url
                ]
            );

            // Logo not modified, keep old url
            if ($response->getStatusCode() === 304) {
                return $favicon_url;
            }

            if (!file_exists($favicon_cache) || filesize($favicon_cache) === 0) {
                return null;
            }
        } catch (RequestException | ConnectException $e) {
            $this->logger->info(
                'An error occurred while trying to download the feed logo of {url}: {error}',
                [
                'url'   => $feed_url,
                'error' => $e->getMessage() ?? 'Unknown'
                ]
            );
            return null;
        }

        // MIME types that are no bitmap images, but can be safely accepted without additional checks
        $allowed_mimes = [
                'image/svg+xml',
                'image/svg'
        ];

        $mime_type = mime_content_type($favicon_cache);
        $is_image = $mime_type !== false && substr($mime_type, 0, 5) === "image";

        // check if file is actually an image
        if (!$is_image) {
            $this->logger->debug(
                "Downloaded file:{file} from {url} is not an image",
                [
                'file' => $favicon_cache,
                'url'   => $favicon_url
                ]
            );
            unlink($favicon_cache);
            return null;
        }

        if (!in_array($mime_type, $allowed_mimes, true)) {
            $image_info = getimagesize($favicon_cache);
            if ($image_info === false) {
                $this->logger->debug(
                    "Could not get image size for file:{file} from {url}",
                    [
                    'file' => $favicon_cache,
                    'url'   => $favicon_url
                    ]
                );
                unlink($favicon_cache);
                return null;
            }

            list($width, $height, $type, $attr) = $image_info;
            // check if image is square else reject it
            if ($width !== $height) {
                $this->logger->debug(
                    "Downloaded file:{file} from {url} is not square",
                    [
                    'file' => $favicon_cache,
                    'url'   => $favicon_url
                    ]
                );
                unlink($favicon_cache);
                return null;
            }
        }

        // file name of the stored logo info is md5 of the feed url
        $favicon_filename = md5($feed_url);
        // copy verified feed icon data to appData
        $this->appData->putFileContent(Constants::LOGO_IMAGE_DIR, $favicon_url_hash, file_get_contents($favicon_cache));
        $this->appData->putFileContent(Constants::LOGO_INFO_DIR, 'img_'.$favicon_filename, $favicon_url_hash);
        $this->appData->putFileContent(Constants::LOGO_INFO_DIR, 'url_'.$favicon_filename, $favicon_url);
        unlink($favicon_cache);

        return $favicon_url;
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
