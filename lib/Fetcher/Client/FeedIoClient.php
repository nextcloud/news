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
use FeedIo\Adapter\ClientInterface;
use FeedIo\Adapter\ResponseInterface;
use FeedIo\Adapter\Guzzle\Response;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

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
     * @param  string                               $url
     * @param  DateTime                            $modifiedSince
     *
     * @return ResponseInterface
     * @throws ServerErrorException|GuzzleException
     * @throws NotFoundException
     */
    public function getResponse(string $url, ?DateTime $modifiedSince = null) : ResponseInterface
    {
        $modifiedSince->setTimezone(new \DateTimeZone('GMT'));
        try {
            $options = [
                'headers' => [
                    'If-Modified-Since' => $modifiedSince->format('D, d M Y H:i:s e')
                ]
            ];

            $start = microtime(true);
            $psrResponse = $this->guzzleClient->request('get', $url, $options);
            $duration = intval(round(microtime(true) - $start, 3) * 1000);

            return new Response($psrResponse, $duration);
        } catch (BadResponseException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case 404:
                    throw new NotFoundException($e->getMessage());
                default:
                    throw new ServerErrorException($e->getResponse());
            }
        }
    }
}
