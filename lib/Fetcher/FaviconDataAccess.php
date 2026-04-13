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
            'timeout' => 10,
            'allow_redirects' => false,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => $this->fetcherConfig->getUserAgent(),
            ],
        ];
    }
}
