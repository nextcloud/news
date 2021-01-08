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
use OCA\News\AppInfo\Application;
use OCA\News\Fetcher\Client\FeedIoClient;
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
     * User agent for the client.
     * @var string
     */
    const DEFAULT_USER_AGENT = 'NextCloud-News/1.0';

    /**
     * Acccept header for the client.
     * @var string
     */
    const DEFAULT_ACCEPT = 'application/rss+xml, application/rdf+xml;q=0.8, ' .
                           'application/atom+xml;q=0.6, application/xml;q=0.4, ' .
                           'text/xml;q=0.4, */*;q=0.2';

    /**
     * FetcherConfig constructor.
     *
     * @param IConfig $config
     */
    public function __construct(IConfig $config)
    {
        $this->client_timeout = $config->getAppValue(
            Application::NAME,
            'feedFetcherTimeout',
            Application::DEFAULT_SETTINGS['feedFetcherTimeout']
        );
        $this->redirects = $config->getAppValue(
            Application::NAME,
            'maxRedirects',
            Application::DEFAULT_SETTINGS['maxRedirects']
        );

        $proxy = $config->getSystemValue('proxy', null);
        if (is_null($proxy)) {
            return $this;
        }

        $url = new \Net_URL2($proxy);

        $creds = $config->getSystemValue('proxyuserpwd', null);
        if ($creds) {
            $auth = explode(':', $creds, 2);
            $url->setUserinfo($auth[0], $auth[1]);
        }

        $this->proxy = $url->getNormalizedURL();

        return $this;
    }

    /**
     * Configure a guzzle client
     *
     * @return ClientInterface Client to guzzle.
     */
    public function getClient(): ClientInterface
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

        $client = new Client($config);
        return new FeedIoClient($client);
    }
}
