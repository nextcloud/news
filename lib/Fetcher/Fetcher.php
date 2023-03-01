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

use FeedIo\Reader\ReadErrorException;

class Fetcher
{

    /**
     * List of fetchers.
     * @var IFeedFetcher[]
     */
    private $fetchers;

    public function __construct()
    {
        $this->fetchers = [];
    }


    /**
     * Add an additional fetcher
     *
     * @param IFeedFetcher $fetcher the fetcher
     */
    public function registerFetcher(IFeedFetcher $fetcher): void
    {
        $this->fetchers[] = $fetcher;
    }

    /**
     * Fetch a feed from remote
     *
     * @param  string      $url               remote url of the feed
     * @param  bool        $fullTextEnabled   If true use a scraper to download the full article
     * @param  string|null $user              if given, basic auth is set for this feed
     * @param  string|null $password          if given, basic auth is set for this feed. Ignored if user is empty
     * @param  string|null $httpLastModified  if given, will be used when sending a request to servers
     *
     * @throws ReadErrorException if FeedIO fails
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch(
        string $url,
        bool $fullTextEnabled = false,
        ?string $user = null,
        ?string $password = null,
        ?string $httpLastModified = null
    ): array {
        foreach ($this->fetchers as $fetcher) {
            if (!$fetcher->canHandle($url)) {
                continue;
            }
            return $fetcher->fetch(
                $url,
                $fullTextEnabled,
                $user,
                $password,
                $httpLastModified
            );
        }

        return [null, []];
    }
}
