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

use FeedIo\Adapter\ClientInterface;
use FeedIo\Adapter\Guzzle\Response;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Guzzle dependent HTTP client
 */
class FeedIoClient implements ClientInterface
{
    /**
     * @var \GuzzleHttp\ClientInterface
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
     * @param  \DateTime                            $modifiedSince
     * @throws \FeedIo\Adapter\NotFoundException
     * @throws \FeedIo\Adapter\ServerErrorException
     * @return \FeedIo\Adapter\ResponseInterface
     */
    public function getResponse($url, \DateTime $modifiedSince)
    {
        try {
            $options = [
                'headers' => [
                    'If-Modified-Since' => $modifiedSince->format(\DateTime::RFC2822)
                ]
            ];

            return new Response($this->guzzleClient->request('get', $url, $options));
        } catch (BadResponseException $e) {
            switch ((int) $e->getResponse()->getStatusCode()) {
                case 404:
                    throw new NotFoundException($e->getMessage());
                default:
                    throw new ServerErrorException($e->getMessage());
            }
        }
    }
}
