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

interface IFeedFetcher
{

    /**
     * Fetch feed content.
     *
     * @param  string  $url               remote url of the feed
     * @param  boolean $getFavicon        if the favicon should also be fetched, defaults to true
     * @param  string  $lastModified      a last modified value from an http header defaults to false.
     *                                    If lastModified matches the http header from the feed no results are fetched
     * @param  string  $user              if given, basic auth is set for this feed
     * @param  string  $password          if given, basic auth is set for this feed. Ignored if user is empty
     *
     * @throws FetcherException if the fetcher encounters a problem
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch($url, $getFavicon = true, $lastModified = null, $user = null, $password = null);

    /**
     * Can a fetcher handle a feed.
     *
     * @param string $url the url that should be fetched
     *
     * @return boolean if the fetcher can handle the url. This fetcher will be
     * used exclusively to fetch the feed and the items of the page
     */
    public function canHandle($url);
}
