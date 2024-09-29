<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Ben Vidulich <ben@vidulich.nz>
 * @copyright 2024 Ben Vidulich
 */

namespace OCA\News\Fetcher;

use Favicon\DataAccess;

use OCA\News\Config\FetcherConfig;

/**
 * Modified version of DataAccess with a configurable user agent header.
 */
class FaviconDataAccess extends DataAccess
{
    /**
     * @var FetcherConfig
     */
    private $fetcherConfig;

    public function __construct(
        FetcherConfig $fetcherConfig,
    ) {
        $this->fetcherConfig  = $fetcherConfig;
    }

    public function retrieveUrl($url)
    {
        $this->setContext();
        return @file_get_contents($url);
    }

    public function retrieveHeader($url)
    {
        $this->setContext();
        $headers = @get_headers($url, 1);
        return is_array($headers) ? array_change_key_case($headers) : [];
    }

    private function setContext()
    {
        stream_context_set_default(
            [
                'http' => [
                    'method' => 'GET',
                    'follow_location' => 0,
                    'max_redirects' => 1,
                    'timeout' => 10,
                    'header' => 'User-Agent: ' . $this->fetcherConfig->getUserAgent(),
                ]
            ]
        );
    }
}
