<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Http;

use OCA\News\Vendor\GuzzleHttp\Psr7\Response;
use OCA\News\Vendor\GuzzleHttp\Psr7\Utils;
use OCA\News\Vendor\Psr\Http\Client\ClientInterface;
use OCA\News\Vendor\Psr\Http\Message\RequestInterface;
use OCA\News\Vendor\Psr\Http\Message\ResponseInterface;
use OCP\Http\Client\IClient;

/**
 * Scoped PSR-18 adapter for Nextcloud's IClient.
 */
class ScopedClient implements ClientInterface
{
    /**
     * @param IClient $nextcloudClient Nextcloud HTTP client
     * @param array $defaultOptions IClient options (timeout, allow_redirects, ...)
     */
    public function __construct(
        private readonly IClient $nextcloudClient,
        private readonly array $defaultOptions = []
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());

        if ($method !== 'get' && $method !== 'head') {
            throw new \InvalidArgumentException(
                'OCA\\News\\Http\\ScopedClient only supports GET and HEAD requests, got: ' . $request->getMethod()
            );
        }

        $options = $this->defaultOptions;
        $options['http_errors'] = false;
        $options['headers'] = [];

        foreach ($request->getHeaders() as $name => $values) {
            $options['headers'][$name] = implode(', ', $values);
        }

        $ncResponse = match ($method) {
            'get' => $this->nextcloudClient->get((string) $request->getUri(), $options),
            'head' => $this->nextcloudClient->head((string) $request->getUri(), $options),
        };

        return new Response(
            $ncResponse->getStatusCode(),
            $ncResponse->getHeaders(),
            Utils::streamFor($ncResponse->getBody() ?? '')
        );
    }
}
