<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Http;

use GuzzleHttp\Psr7\Request as UnscopedRequest;
use OCA\News\Vendor\GuzzleHttp\Psr7\Response;
use OCA\News\Vendor\GuzzleHttp\Psr7\Utils;
use OCA\News\Vendor\Psr\Http\Client\ClientInterface;
use OCA\News\Vendor\Psr\Http\Message\RequestInterface;
use OCA\News\Vendor\Psr\Http\Message\ResponseInterface;
use OCP\Http\Client\IClient;
use Psr\Http\Message\RequestInterface as UnscopedRequestInterface;

/**
 * Scoped PSR-18 adapter for Nextcloud's IClient.
 */
class ScopedClient implements ClientInterface
{
    private readonly Client $client;

    /**
     * @param IClient $nextcloudClient Nextcloud HTTP client
     * @param array $defaultOptions IClient options (timeout, allow_redirects, ...)
     *                              Must NOT contain a 'headers' key - request headers are
     *                              taken exclusively from the PSR-7 request.
     */
    public function __construct(
        IClient $nextcloudClient,
        array $defaultOptions = []
    ) {
        // Reuse the unscoped adapter so request/response option semantics are defined in one place.
        // @phpstan-ignore-next-line new.deprecated
        $this->client = new Client($nextcloudClient, $defaultOptions);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->client->sendRequest($this->toUnscopedRequest($request));
        } catch (\InvalidArgumentException $e) {
            throw new ScopedRequestException($request, $e->getMessage(), (int) $e->getCode(), $e);
        } catch (\Throwable $e) {
            throw new ScopedNetworkException($request, $e->getMessage(), (int) $e->getCode(), $e);
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            Utils::streamFor((string) $response->getBody()),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    private function toUnscopedRequest(RequestInterface $request): UnscopedRequestInterface
    {
        return new UnscopedRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            $request->getHeaders(),
            (string) $request->getBody(),
            $request->getProtocolVersion()
        );
    }
}
