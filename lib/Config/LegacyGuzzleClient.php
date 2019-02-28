<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Sean Molenaar <smillernl@me.com>
 * @copyright 2018 Sean Molenaar
 */

namespace OCA\News\Config;

use FeedIo\Adapter\ClientInterface as FeedIoClientInterface;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ServerErrorException;
use Guzzle\Service\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Guzzle dependent HTTP client
 */
class LegacyGuzzleClient implements FeedIoClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $guzzleClient;

    /**
     * @param ClientInterface $guzzleClient
     */
    public function __construct(ClientInterface $guzzleClient)
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
                    'User-Agent' => 'NextCloud-News/1.0',
                    'If-Modified-Since' => $modifiedSince->format(\DateTime::RFC2822)
                ]
            ];

            return new LegacyGuzzleResponse($this->guzzleClient->get($url, $options));
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
