<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Fetcher;


class YoutubeFetcher implements IFeedFetcher {

    private $feedFetcher;

    public function __construct(FeedFetcher $feedFetcher){
        $this->feedFetcher = $feedFetcher;
    }


    private function buildUrl($url) {
        $baseRegex = '%(?:https?://|//)?(?:www.)?youtube.com';
        $playRegex = $baseRegex . '.*?list=([^&]*)%';

        if (preg_match($playRegex, $url, $matches)) {
            $id = $matches[1];
            return 'http://gdata.youtube.com/feeds/api/playlists/' . $id;
        } else {
            return $url;
        }
    }


    /**
     * This fetcher handles all the remaining urls therefore always returns true
     */
    public function canHandle($url){
        return $this->buildUrl($url) !== $url;
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
     * @throws FetcherException if it fails
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch($url, $getFavicon=true, $lastModified=null,
                          $etag=null) {
        $transformedUrl = $this->buildUrl($url);

        $result = $this->feedFetcher->fetch(
            $transformedUrl, $getFavicon, $lastModified, $etag
        );

        // reset feed url so we know the correct added url for the feed
        $result[0]->setUrl($url);

        return $result;
    }


}
