<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Http;

// Intentionally unscoped: these classes are provided by the Nextcloud server's
// bundled Guzzle copy at runtime. Using the server's version is correct here
// because this adapter wraps IClient (also a server class).
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use OCP\Http\Client\IClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 adapter wrapping Nextcloud's IClient.
 *
 * Nextcloud's IClientService provides SSRF protection and reads the system
 * proxy configuration automatically. Because IClient does not implement the
 * PSR-18 ClientInterface directly, this adapter bridges the two so that
 * feed-io (and any other PSR-18-aware library) can benefit from those
 * safeguards without waiting for native server-side support.
 *
 * Inspired by the equivalent adapter in the Nextcloud Bookmarks app.
 *
 * @deprecated Replace with native IClient/PSR-18 support once NC 34 is the minimum version.
 */
class Client implements ClientInterface
{
    /**
     * @param IClient $nextcloudClient  Nextcloud HTTP client
     * @param array   $defaultOptions   IClient-level options (e.g. timeout, allow_redirects).
     *                                  Must NOT contain a 'headers' key — headers are taken
     *                                  exclusively from the PSR-7 request.
     */
    public function __construct(
        private readonly IClient $nextcloudClient,
        private readonly array $defaultOptions = []
    ) {
    }

    /**
     * Sends a PSR-7 request via Nextcloud's IClient and returns a PSR-7 response.
     *
     * Only GET and HEAD are supported; feed fetching never requires other methods.
     *
     * @throws \InvalidArgumentException for unsupported HTTP methods
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());

        if ($method !== 'get' && $method !== 'head') {
            throw new \InvalidArgumentException(
                'OCA\\News\\Http\\Client only supports GET and HEAD requests, got: ' . $request->getMethod()
            );
        }

        // Start from the configured IClient options (timeout, allow_redirects, etc.).
        // Headers come exclusively from the PSR-7 request and are never merged with
        // defaultOptions — any 'headers' key there would be silently ignored.
        // http_errors is forced to false: PSR-18 requires sendRequest() to return
        // the response even for 4xx/5xx; callers are responsible for status-code handling.
        $options = $this->defaultOptions;
        $options['http_errors'] = false;
        $options['headers'] = [];
        foreach ($request->getHeaders() as $name => $values) {
            $options['headers'][$name] = implode(', ', $values);
        }

        /** @var \OCP\Http\Client\IResponse $ncResponse */
        $ncResponse = match ($method) {
            'get'  => $this->nextcloudClient->get((string) $request->getUri(), $options),
            'head' => $this->nextcloudClient->head((string) $request->getUri(), $options),
        };

        return new Response(
            $ncResponse->getStatusCode(),
            $ncResponse->getHeaders(),
            Utils::streamFor($ncResponse->getBody() ?? '')
        );
    }
}
