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

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Utility\PicoFeedFaviconFactory;
use \OCA\News\Utility\PicoFeedReaderFactory;

class FeedFetcher implements IFeedFetcher {

    private $faviconFactory;
    private $readerFactory;
    private $time;

    public function __construct(PicoFeedReaderFactory $readerFactory,
                                PicoFeedFaviconFactory $faviconFactory,
                                $time){
        $this->faviconFactory = $faviconFactory;
        $this->readerFactory = $readerFactory;
        $this->time = $time;
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
     * @throws FetcherException if simple pie fails
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch($url, $getFavicon=true, $lastModified=null,
                          $etag=null) {
        $reader = $this->readerFactory->build();
        $resource = $reader->download($url, $lastModified, $etag);

        $modified = $resource->getLastModified();
        $etag = $resource->getEtag();
        $location = $resource->getUrl();

        if (!$resource->isModified()) {
            return [null, null];
        }

        try {
            $parser = $reader->getParser();

            if (!$parser) {
                throw new \Exception(
                    'Could not find a valid feed at url ' . $url
                );
            }
            $parsedFeed = $parser->execute();

            if (!$parsedFeed) {
                throw new \Exception(
                    'Could not parse feed ' . $url
                );
            }

            $items = [];
            foreach($parsedFeed->getItems() as $item) {
                $items[] = $this->buildItem($item);
            }

            $feed = $this->buildFeed(
                $parsedFeed, $url, $getFavicon, $modified, $etag, $location
            );

            return [$feed, $items];

        } catch(\Exception $ex){
            throw new FetcherException($ex->getMessage());
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
        $item->setPubDate($parsedItem->getDate());
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

        return $item;
    }


    protected function buildFeed($parsedFeed, $url, $getFavicon, $modified,
                                 $etag, $location) {
        $feed = new Feed();

        $link = $parsedFeed->getUrl();

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
