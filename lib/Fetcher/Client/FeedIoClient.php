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
use OCA\News\Vendor\FeedIo\Adapter\ClientInterface;
use OCA\News\Vendor\FeedIo\Adapter\ResponseInterface;
use OCA\News\Vendor\FeedIo\Adapter\Http\Response;
use OCA\News\Vendor\FeedIo\Adapter\HttpRequestException;
use OCA\News\Vendor\FeedIo\Adapter\NotFoundException;
use OCA\News\Vendor\FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use OCA\News\Vendor\GuzzleHttp\Psr7\Response as ScopedPsr7Response;

/**
 * Guzzle dependent HTTP client
 */
class FeedIoClient implements ClientInterface
{
    /**
     * @param \GuzzleHttp\ClientInterface $guzzleClient
     */
    protected $guzzleClient;

    /**
     * @param \GuzzleHttp\ClientInterface $guzzleClient
     */
    public function __construct(\GuzzleHttp\ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
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
     * @param  string                                   $url
     * @param  DateTime|null                            $modifiedSince
     *
     * @return ResponseInterface
     * @throws ServerErrorException|GuzzleException
     * @throws NotFoundException
     */
    public function getResponse(string $url, ?DateTime $modifiedSince = null) : ResponseInterface
    {
        try {
            $options = [
                'headers' => []
            ];

            if ($modifiedSince !== null && $modifiedSince->format('U') >= 0) {
                $modifiedSince->setTimezone(new \DateTimeZone('GMT'));
                $options['headers']['If-Modified-Since'] = $modifiedSince->format('D, d M Y H:i:s') . ' GMT';
            }

            $start = microtime(true);
            $psrResponse = $this->guzzleClient->request('get', $url, $options);
            $duration = intval(round(microtime(true) - $start, 3) * 1000);

            return new Response($this->wrapResponse($psrResponse), $duration);
        } catch (BadResponseException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case 403:
                    throw new HttpRequestException($e->getMessage());
                case 404:
                    throw new NotFoundException($e->getMessage());
                default:
                    throw new ServerErrorException($this->wrapResponse($e->getResponse()));
            }
        }
    }
}
