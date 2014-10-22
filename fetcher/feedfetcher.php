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

use \PicoFeed\Favicon;
use \PicoFeed\Reader;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;

class FeedFetcher implements IFeedFetcher {

    private $faviconFetcher;
    private $reader;
    private $time;

    public function __construct(Reader $reader, Favicon $faviconFetcher, $time){
        $this->faviconFetcher = $faviconFetcher;
        $this->reader = $reader;
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
        $resource = $this->reader->download($url, $lastModified, $etag);

        $modified = $resource->getLastModified();
        $etag = $resource->getEtag();

        if (!$resource->isModified()) {
            return [null, null];
        }

        try {
            $parser = $this->reader->getParser();

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

            $link = $parsedFeed->getUrl();
            foreach($parsedFeed->getItems() as $item) {
                $items[] = $this->buildItem($item);
            }

            $feed = $this->buildFeed(
                $parsedFeed, $url, $getFavicon, $modified, $etag
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
        $item->setStatus(0);
        $item->setUnread();
        $url = $this->decodeTwice($parsedItem->getUrl());
        $item->setUrl($url);

        // unescape content because angularjs helps against XSS
        $item->setTitle($this->decodeTwice($parsedItem->getTitle()));
        $guid = $parsedItem->getId();
        $item->setGuid($guid);

        // purification is done in the service layer
        $body = $parsedItem->getContent();
        $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
        $item->setBody($body);

        // pubdate is not required. if not given use the current date
        $date = $parsedItem->getDate();

        $item->setPubDate($date);

        $item->setLastModified($this->time->getTime());

        $author = $parsedItem->getAuthor();
        $item->setAuthor($this->decodeTwice($author));

        // TODO: make it work for video files also
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
                                 $etag) {
        $feed = new Feed();

        // unescape content because angularjs helps against XSS
        $title = strip_tags($this->decodeTwice($parsedFeed->getTitle()));

        // if there is no title use the url
        if(!$title) {
            $title = $url;
        }

        $feed->setTitle($title);
        $feed->setUrl($url);
        $feed->setLastModified($modified);
        $feed->setEtag($etag);

        $link = $parsedFeed->getUrl();
        if (!$link) {
            $link = $url;
        }
        $feed->setLink($link);

        $feed->setAdded($this->time->getTime());

        if ($getFavicon) {
            $favicon = $this->faviconFetcher->find($feed->getLink());
            $feed->setFaviconLink($favicon);
        }

        return $feed;
    }

}
