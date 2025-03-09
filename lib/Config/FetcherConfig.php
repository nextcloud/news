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

use OCA\News\Vendor\FeedIo\Adapter\ClientInterface;
use Psr\Log\LoggerInterface;
use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Uri;
use OCA\News\AppInfo\Application;
use OCA\News\Fetcher\Client\FeedIoClient;
use OCP\IConfig;
use OCP\IAppConfig;
use OCP\App\IAppManager;

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
     * Version number for the news application.
     * @var string
     */
    private $version;

    /**
     * User agent for the client.
     * @var string
     */
    const DEFAULT_USER_AGENT = 'NextCloud-News/1.0';

    /**
     * Accept header for the client.
     * @var string
     */
    const DEFAULT_ACCEPT = 'application/rss+xml, application/rdf+xml;q=0.8, ' .
                           'application/atom+xml;q=0.6, application/xml;q=0.4, ' .
                           'text/xml;q=0.4, */*;q=0.2';

    /**
     * Duration after which the feed is considered sleepy.
     * @var int
     */
    public const SLEEPY_DURATION = 7 * 86400;

    /**
     * Logger
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * FetcherConfig constructor.
     *
     * @param IAppConfig $config    App configuration
     * @param IConfig $systemconfig System configuration
     * @param IAppManager $appManager App manager
     * @param LoggerInterface $logger Logger
     */
    public function __construct(
        IAppConfig $config,
        IConfig $systemconfig,
        IAppManager $appManager,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->version = $appManager->getAppVersion(Application::NAME);
        $this->client_timeout = $config->getValueInt(
            Application::NAME,
            'feedFetcherTimeout',
            Application::DEFAULT_SETTINGS['feedFetcherTimeout']
        );
        $this->redirects = $config->getValueInt(
            Application::NAME,
            'maxRedirects',
            Application::DEFAULT_SETTINGS['maxRedirects']
        );

        $proxy = $systemconfig->getSystemValue('proxy', null);
        if (is_null($proxy)) {
            $this->logger->debug('No proxy configuration found');
            return $this;
        }
        $this->logger->debug(
            'Proxy configuration found: {proxy}',
            ['proxy' => $proxy]
        );

        $url = new Uri($proxy);

        $creds = $systemconfig->getSystemValue('proxyuserpwd', null);
        if ($creds !== null) {
            $auth = explode(':', $creds, 2);
            $url = $url->withUserInfo($auth[0], $auth[1]);
        }

        //Uri removes standard ports by default, therefore we add it manually
        $url_string = (string) $url;
        $scheme = $url->getScheme();
        $port = $url->getPort();

        if ($port === null && $scheme === 'http') {
            $port = 80;
        } elseif ($port === null && $scheme === 'https') {
            $port = 443;
        }

        if ($port !== null && strpos($url_string, ':' . $port) === false) {
            $url_string .= ':' . $port;
        }

        $this->proxy = $url_string;

        $this->logger->debug(
            'Proxy configuration finalized: {proxy}',
            ['proxy' => $this->proxy]
        );

        return $this;
    }

    /**
     * Checks for available encoding options
     *
     * @return string list of supported encoding types
     */
    public function checkEncoding(): string
    {
        $supportedEncoding = [];

        // check curl features
        $curl_features = curl_version()["features"];

        $bitfields = array('CURL_VERSION_LIBZ' => ['gzip', 'deflate'], 'CURL_VERSION_BROTLI' => ['br']);

        foreach ($bitfields as $feature => $header) {
            // checking available features via the 'features' bitmask and adding available types to the list
            if (defined($feature) && $curl_features & constant($feature)) {
                $supportedEncoding = array_merge($supportedEncoding, $header);
            }
        }
        return implode(", ", $supportedEncoding);
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
            'headers' =>  [
                'User-Agent' => $this->getUserAgent(),
                'Accept' => static::DEFAULT_ACCEPT,
                'Accept-Encoding' => $this->checkEncoding()
            ],
        ];

        if (!is_null($this->proxy)) {
            $config['proxy'] = $this->proxy;
        }
        if (!is_null($this->redirects)) {
            $config['redirect.max'] = $this->redirects;
        }

        $client = new Client($config);
        return new FeedIoClient($client);
    }

    /**
     * Gets a user agent name for the client
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        if (is_null($this->version)) {
            return self::DEFAULT_USER_AGENT;
        }

        return 'NextCloud-News/' . $this->version;
    }

    /**
     * Get the proxy configuration
     *
     * @return string|null
     */
    public function getProxy(): ?string
    {
        return $this->proxy;
    }
}
