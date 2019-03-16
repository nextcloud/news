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
use \FeedIo\Adapter\Guzzle\Client as FeedIoClient;
use OCP\IConfig;

/**
 * Class FetcherConfig
 *
 * @package OCA\News\Config
 */
class FetcherConfig
{
    protected $client_timeout;
    protected $proxy;
    protected $redirects;
    protected $max_size;

    /**
     * Configure a guzzle client
     *
     * @return ClientInterface Legacy client to guzzle.
     */
    public function getClient()
    {
        if (!class_exists('GuzzleHttp\Collection')) {
            $config = [
                'timeout' => $this->client_timeout,
            ];

            if (!empty($this->proxy)) {
                $config['proxy'] = $this->proxy;
            }

            if (!empty($this->redirects)) {
                $config['redirect.max'] = $this->redirects;
            }

            $guzzle = new Client($config);
            $client = new FeedIoClient($guzzle);

            return $client;
        }

        $config = [
            'request.options' => [
                'timeout' => $this->client_timeout,
            ],
        ];

        if (!empty($this->proxy)) {
            $config['request.options']['proxy'] = $this->proxy;
        }

        if (!empty($this->redirects)) {
            $config['request.options']['redirect.max'] = $this->redirects;
        }

        $guzzle = new Client($config);
        return new LegacyGuzzleClient($guzzle);
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
