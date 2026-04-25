<?php
/*
 * This file is part of the feed-io package.
 *
 * (c) Alexandre Debril <alex.debril@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OCA\News\Fetcher\Client;

use DateTime;
use OCA\News\Http\Client as NextcloudHttpClient;
use OCA\News\Vendor\FeedIo\Adapter\ClientInterface;
use OCA\News\Vendor\FeedIo\Adapter\ResponseInterface;
use OCA\News\Vendor\FeedIo\Adapter\Http\Response;
use OCA\News\Vendor\FeedIo\Adapter\HttpRequestException;
use OCA\News\Vendor\FeedIo\Adapter\NotFoundException;
use OCA\News\Vendor\FeedIo\Adapter\ServerErrorException;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\LocalServerException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
// Intentionally unscoped: GuzzleHttp\Psr7\Request is provided by the Nextcloud
// server's bundled Guzzle copy at runtime, consistent with using IClientService.
use GuzzleHttp\Psr7\Request;
use OCA\News\Vendor\GuzzleHttp\Psr7\Response as ScopedPsr7Response;

/**
 * HTTP client adapter for feed-io, backed by Nextcloud's IClientService.
 *
 * Using IClientService provides automatic SSRF protection and reads the
 * system proxy settings configured in Nextcloud's admin panel.
 */
class FeedIoClient implements ClientInterface
{
    protected readonly PsrClientInterface $httpClient;

    /**
     * @param IClientService $clientService   Nextcloud HTTP client service
     * @param array          $defaultHeaders  Default request headers (User-Agent, Accept, …)
     * @param array          $requestOptions  IClient-level options forwarded to the PSR-18 adapter
     *                                        (e.g. timeout, connect_timeout, allow_redirects).
     */
    public function __construct(
        IClientService $clientService,
        private readonly array $defaultHeaders = [],
        private readonly array $requestOptions = []
    ) {
        $iClient = $clientService->newClient();
        // @phpstan-ignore new.deprecated
        $this->httpClient = new NextcloudHttpClient($iClient, $this->requestOptions);
    }

    /**
     * Wrap an unscoped PSR-7 response into a scoped one
     *
     * @param \Psr\Http\Message\ResponseInterface $unscopedResponse
     * @return \OCA\News\Vendor\Psr\Http\Message\ResponseInterface
     */
    private function wrapResponse(
        \Psr\Http\Message\ResponseInterface $unscopedResponse
    ): \OCA\News\Vendor\Psr\Http\Message\ResponseInterface {
        return new ScopedPsr7Response(
            $unscopedResponse->getStatusCode(),
            $unscopedResponse->getHeaders(),
            $unscopedResponse->getBody(),
            $unscopedResponse->getProtocolVersion(),
            $unscopedResponse->getReasonPhrase()
        );
    }

    /**
     * @param  string        $url
     * @param  DateTime|null $modifiedSince
     *
     * @return ResponseInterface
     * @throws ServerErrorException
     * @throws NotFoundException
     * @throws HttpRequestException
     */
    public function getResponse(string $url, ?DateTime $modifiedSince = null) : ResponseInterface
    {
        try {
            $headers = $this->defaultHeaders;
            if ($modifiedSince !== null && $modifiedSince->format('U') >= 0) {
                $modifiedSince->setTimezone(new \DateTimeZone('GMT'));
                $headers['If-Modified-Since'] = $modifiedSince->format('D, d M Y H:i:s') . ' GMT';
            }

            $start = microtime(true);
            $request = new Request('GET', $url, $headers);
            $psrResponse = $this->httpClient->sendRequest($request);
            $duration = intval(round(microtime(true) - $start, 3) * 1000);

            $status = $psrResponse->getStatusCode();
            if ($status === 404) {
                throw new NotFoundException('HTTP 404 Not Found: ' . $url);
            }
            if ($status >= 400 && $status < 500) {
                throw new HttpRequestException('HTTP ' . $status . ' Client Error: ' . $url);
            }
            if ($status >= 500) {
                throw new ServerErrorException($this->wrapResponse($psrResponse));
            }

            return new Response($this->wrapResponse($psrResponse), $duration);
        } catch (LocalServerException $e) {
            throw new HttpRequestException('Local server access is not allowed: ' . $e->getMessage());
        } catch (ClientExceptionInterface $e) {
            throw new HttpRequestException('Transport error for ' . $url . ': ' . $e->getMessage());
        }
    }
}
