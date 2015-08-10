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

namespace OCA\News\Fetcher;

use PicoFeed\Parser\MalFormedXmlException;
use PicoFeed\Reader\Reader;
use PicoFeed\Reader\SubscriptionNotFoundException;
use PicoFeed\Reader\UnsupportedFeedFormatException;
use PicoFeed\Client\InvalidCertificateException;
use PicoFeed\Client\InvalidUrlException;
use PicoFeed\Client\MaxRedirectException;
use PicoFeed\Client\MaxSizeException;
use PicoFeed\Client\TimeoutException;

use OCP\IL10N;
use OCP\AppFramework\Utility\ITimeFactory;

use OCA\News\Db\Item;
use OCA\News\Db\Feed;
use OCA\News\Utility\PicoFeedFaviconFactory;
use OCA\News\Utility\PicoFeedReaderFactory;

class FeedFetcher implements IFeedFetcher {

    private $faviconFactory;
    private $reader;
    private $l10n;
    private $time;

    public function __construct(Reader $reader,
                                PicoFeedFaviconFactory $faviconFactory,
                                IL10N $l10n,
                                ITimeFactory $time){
        $this->faviconFactory = $faviconFactory;
        $this->reader = $reader;
        $this->time = $time;
        $this->l10n = $l10n;
    }


    /**
     * This fetcher handles all the remaining urls therefore always returns true
     */
    public function canHandle($url){
        return true;
    }


    /**
     * Fetch a feed from remote
     * @param string $url remote url of the feed
     * @param boolean $getFavicon if the favicon should also be fetched,
     * defaults to true
     * @param string $lastModified a last modified value from an http header
     * defaults to false. If lastModified matches the http header from the feed
     * no results are fetched
     * @param string $etag an etag from an http header.
     * If lastModified matches the http header from the feed
     * no results are fetched
     * @param bool fullTextEnabled if true tells the fetcher to enhance the
     * articles by fetching custom enhanced content
     * @throws FetcherException if it fails
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch($url, $getFavicon=true, $lastModified=null,
                          $etag=null, $fullTextEnabled=false) {
        try {
            $resource = $this->reader->discover($url, $lastModified, $etag);

            if (!$resource->isModified()) {
                return [null, null];
            }

            $location = $resource->getUrl();
            $etag = $resource->getEtag();
            $content = $resource->getContent();
            $encoding = $resource->getEncoding();
            $lastModified = $resource->getLastModified();

            $parser = $this->reader->getParser($location, $content, $encoding);

            if ($fullTextEnabled) {
                $parser->enableContentGrabber();
            }

            $parsedFeed = $parser->execute();

            $feed = $this->buildFeed(
                $parsedFeed, $url, $getFavicon, $lastModified, $etag, $location
            );

            $items = [];
            foreach($parsedFeed->getItems() as $item) {
                $items[] = $this->buildItem($item);
            }

            return [$feed, $items];

        } catch(\Exception $ex){
            $msg = $ex->getMessage();

            if ($ex instanceof MalFormedXmlException) {
                $msg = $this->l10n->t('Feed contains invalid XML');
            } else if ($ex instanceof SubscriptionNotFoundException) {
                $msg = $this->l10n->t('Could not find a feed');
            } else if ($ex instanceof UnsupportedFeedFormatException) {
                $msg = $this->l10n->t('Detected feed format is not supported');
            } else if ($ex instanceof InvalidCertificateException) {
                $msg = $this->l10n->t('SSL Certificate is invalid');
            } else if ($ex instanceof InvalidUrlException) {
                $msg = $this->l10n->t('Website not found');
            } else if ($ex instanceof MaxRedirectException) {
                $msg = $this->l10n->t('More redirects than allowed, aborting');
            } else if ($ex instanceof MaxSizeException) {
                $msg = $this->l10n->t('Bigger than maximum allowed size');
            } else if ($ex instanceof TimeoutException) {
                $msg = $this->l10n->t('Request timed out');
            }

            throw new FetcherException($msg);
        }

    }


    private function decodeTwice($string) {
        // behold! &apos; is not converted by PHP that's why we need to do it
        // manually (TM)
        return str_replace('&apos;', '\'',
                html_entity_decode(
                    html_entity_decode(
                        $string, ENT_QUOTES, 'UTF-8'
                    ),
                ENT_QUOTES, 'UTF-8'
            )
        );
    }


    protected function buildItem($parsedItem) {
        $item = new Item();
        $item->setUnread();
        $item->setUrl($parsedItem->getUrl());
        $item->setGuid($parsedItem->getId());
        $item->setGuidHash($item->getGuid());
        $item->setPubDate($parsedItem->getDate()->getTimestamp());
        $item->setLastModified($this->time->getTime());

        // unescape content because angularjs helps against XSS
        $item->setTitle($this->decodeTwice($parsedItem->getTitle()));
        $item->setAuthor($this->decodeTwice($parsedItem->getAuthor()));

        // purification is done in the service layer
        $body = $parsedItem->getContent();
        $body = mb_convert_encoding($body, 'HTML-ENTITIES',
            mb_detect_encoding($body));
        $item->setBody($body);

        $enclosureUrl = $parsedItem->getEnclosureUrl();
        if($enclosureUrl) {
            $enclosureType = $parsedItem->getEnclosureType();
            if(stripos($enclosureType, 'audio/') !== false ||
               stripos($enclosureType, 'video/') !== false) {
                $item->setEnclosureMime($enclosureType);
                $item->setEnclosureLink($enclosureUrl);
            }
        }

        $item->generateSearchIndex();

        return $item;
    }


    protected function buildFeed($parsedFeed, $url, $getFavicon, $modified,
                                 $etag, $location) {
        $feed = new Feed();

        $link = $parsedFeed->getSiteUrl();

        if (!$link) {
            $link = $location;
        }

        // unescape content because angularjs helps against XSS
        $title = strip_tags($this->decodeTwice($parsedFeed->getTitle()));
        $feed->setTitle($title);
        $feed->setUrl($url);  // the url used to add the feed
        $feed->setLocation($location);  // the url where the feed was found
        $feed->setLink($link);  // <link> attribute in the feed
        $feed->setLastModified($modified);
        $feed->setEtag($etag);
        $feed->setAdded($this->time->getTime());

        if ($getFavicon) {
            $faviconFetcher = $this->faviconFactory->build();
            $favicon = $faviconFetcher->find($feed->getLink());
            $feed->setFaviconLink($favicon);
        }

        return $feed;
    }

}
