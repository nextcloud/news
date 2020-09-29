<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Fetcher;

class YoutubeFetcher implements IFeedFetcher
{

    private $feedFetcher;

    public function __construct(FeedFetcher $feedFetcher)
    {
        $this->feedFetcher = $feedFetcher;
    }


    /**
     * Build YouTube URL
     *
     * @param string $url
     *
     * @return string
     */
    private function buildUrl(string $url): string
    {
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
     * Check if the URL is a youtube URL by reformatting it.
     *
     * @param string $url the url that should be fetched
     *
     * @return bool
     */
    public function canHandle(string $url): bool
    {
        return $this->buildUrl($url) !== $url;
    }


    /**
     * Fetch a feed from remote
     *
     * @inheritdoc
     */
    public function fetch(
        string $url,
        bool $favicon,
        ?string $lastModified,
        bool $fullTextEnabled,
        ?string $user,
        ?string $password
    ): array {
        $transformedUrl = $this->buildUrl($url);

        $result = $this->feedFetcher->fetch(
            $transformedUrl,
            $favicon,
            $lastModified,
            $fullTextEnabled,
            $user,
            $password
        );

        // reset feed url so we know the correct added url for the feed
        $result[0]->setUrl($url);

        return $result;
    }
}
