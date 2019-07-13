<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Config;

use FeedIo\Adapter\ClientInterface;
use \GuzzleHttp\Client;
use OCA\News\Fetcher\Client\FeedIoClient;
use OCA\News\Fetcher\Client\LegacyGuzzleClient;
use OCP\IConfig;

/**
 * Class FetcherConfig
 *
 * @package OCA\News\Config
 */
class FetcherConfig
{
    /**
     * Timeout before the client should abort.
     * @var string
     */
    protected $client_timeout;

    /**
     * Configuration for an HTTP proxy.
     * @var string
     */
    protected $proxy;

    /**
     * Amount of allowed redirects.
     * @var string
     */
    protected $redirects;

    /**
     * Max size of the recieved data.
     * @deprecated guzzle can't handle this
     * @var string
     */
    protected $max_size;

    /**
     * User agent for the client.
     * @var string
     */
    const DEFAULT_USER_AGENT = 'NextCloud-News/1.0';

    /**
     * Acccept header for the client.
     * @var string
     */
    const DEFAULT_ACCEPT = 'application/rss+xml, application/rdf+xml;q=0.8, application/atom+xml;q=0.6, application/xml;q=0.4, text/xml;q=0.4, */*;q=0.2';

    /**
     * Configure a guzzle client
     *
     * @return ClientInterface Legacy client to guzzle.
     */
    public function getClient()
    {
        if (!class_exists('GuzzleHttp\Collection')) {
            return new FeedIoClient($this->getConfig());
        }

        return new LegacyGuzzleClient($this->getOldConfig());
    }
    /**
     * Get configuration for modern guzzle.
     * @return Client Guzzle client.
     */
    private function getConfig()
    {
        $config = [
            'timeout' => $this->client_timeout,
            'headers' =>  ['User-Agent' => static::DEFAULT_USER_AGENT, 'Accept' => static::DEFAULT_ACCEPT],
        ];

        if (!empty($this->proxy)) {
            $config['proxy'] = $this->proxy;
        }
        if (!empty($this->redirects)) {
            $config['redirect.max'] = $this->redirects;
        }

        $guzzle = new Client($config);
        return $guzzle;
    }

    /**
     * Get configuration for old guzzle.
     * @return Client Guzzle client.
     */
    private function getOldConfig()
    {
        $config = [
            'request.options' => [
                'timeout' => $this->client_timeout,
                'headers' =>  ['User-Agent' => static::DEFAULT_USER_AGENT],
            ],
        ];

        if (!empty($this->proxy)) {
            $config['request.options']['proxy'] = $this->proxy;
        }

        if (!empty($this->redirects)) {
            $config['request.options']['redirect.max'] = $this->redirects;
        }

        $guzzle = new Client($config);
        return $guzzle;
    }

    /**
     * Set settings for config.
     *
     * @param Config $config The shared configuration
     *
     * @return self
     */
    public function setConfig(Config $config)
    {
        $this->client_timeout = $config->getFeedFetcherTimeout();
        $this->redirects = $config->getMaxRedirects();
        $this->max_size = $config->getMaxSize();

        return $this;
    }

    /**
     * Set the proxy
     *
     * @param IConfig $config Nextcloud config.
     *
     * @return self
     */
    public function setProxy(IConfig $config)
    {
        $proxy = $config->getSystemValue('proxy', null);
        $creds = $config->getSystemValue('proxyuserpwd', null);

        if (is_null($proxy)) {
            return $this;
        }

        $url = new \Net_URL2($proxy);

        if ($creds) {
            $auth = explode(':', $creds, 2);
            $url->setUserinfo($auth[0], $auth[1]);
        }

        $this->proxy = $url->getNormalizedURL();

        return $this;
    }
}
