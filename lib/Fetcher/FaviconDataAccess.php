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

use OCA\News\Vendor\Favicon\DataAccess;

use OCP\Http\Client\IClientService;

use OCA\News\Config\FetcherConfig;
use Psr\Log\LoggerInterface;

/**
 * Modified version of DataAccess with a configurable user agent header.
 *
 * TODO: Replace this class entirely by in-sourcing the favicon lookup logic
 * directly into FeedFetcher. The arthurhoaro/favicon library has tight
 * internal coupling (redirect-following loop, MIME-type validation, etc.) that
 * requires the data-access layer to behave like plain PHP file_get_contents /
 * get_headers (e.g. returning raw bodies for any HTTP status code). That makes
 * it hard to wrap cleanly with a PSR-18 / IClientService adapter. In-sourcing
 * will also let us implement prioritised icon discovery (apple-touch-icon,
 * Open Graph image, etc.) and cache with Nextcloud's AppData rather than the
 * filesystem.
 */
class FaviconDataAccess extends DataAccess
{
    public function __construct(
        private readonly FetcherConfig $fetcherConfig,
        private readonly IClientService $clientService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function retrieveUrl($url)
    {
        try {
            $response = $this->clientService->newClient()->get(
                $url,
                $this->getRequestOptions()
            );
            return $response->getBody();
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Could not fetch favicon URL {url}: {error}',
                ['url' => $url, 'error' => $e->getMessage()]
            );
            return false;
        }
    }

    public function retrieveHeader($url)
    {
        try {
            $response = $this->clientService->newClient()->head(
                $url,
                $this->getRequestOptions()
            );
            $statusCode = $response->getStatusCode();
            $headers = array_change_key_case($response->getHeaders());
            // The Favicon library expects $headers[0] to be the HTTP status line,
            // matching the format returned by PHP's get_headers().
            // Prepend it to ensure correct ordering.
            $headers = [0 => 'HTTP/1.1 ' . $statusCode] + $headers;
            return $headers;
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Could not fetch favicon headers for {url}: {error}',
                ['url' => $url, 'error' => $e->getMessage()]
            );
            return [];
        }
    }

    private function getRequestOptions(): array
    {
        return [
            'timeout'         => 10,
            'allow_redirects' => false,
            // http_errors must remain false: the Favicon library's redirect-following
            // loop in info() parses the status code from the response headers directly.
            // If we throw on 4xx/5xx the library cannot determine the final URL and
            // the whole favicon lookup silently fails.
            'http_errors'     => false,
            'headers'         => [
                'User-Agent' => $this->fetcherConfig->getUserAgent(),
            ],
        ];
    }
}
