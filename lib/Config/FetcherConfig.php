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
use Psr\Http\Client\ClientInterface as PsrClientInterface;
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
    protected readonly string $client_timeout;

    /**
     * Configuration for an HTTP proxy.
     * @var string
     */
    protected readonly string $proxy;

    /**
     * Amount of allowed redirects.
     * @var string
     */
    protected readonly string $redirects;

    /**
     * Version number for the news application.
     * @var string
     */
    private readonly string $version;

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
     * Connect timeout for the guzzle http client
     * @var int
     */
    public const CONNECT_TIMEOUT = 3;

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
        private readonly LoggerInterface $logger,
        private readonly IClientService $clientService,
    ) {
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
     * Configure a feedio client
     *
     * @return ClientInterface Client to feedio client.
     */
    public function getClient(): ClientInterface
    {
        $config = [
            'headers' =>  [
                'Accept' => static::DEFAULT_ACCEPT,
                'Accept-Encoding' => $this->checkEncoding()
            ],
        ];
        $client = $this->getHttpClient($config);
        return new FeedIoClient($client);
    }

    /**
     * Configure a guzzle client
     *
     * @param array $config
     * @return PsrClientInterface configured Guzzle HTTP client
     */
    public function getHttpClient(array $config): PsrClientInterface
    {
        $defaultConfig = [
            'headers' => [
                'User-Agent' => $this->getUserAgent(),
            ],
            'timeout' => $this->client_timeout,
            'connect_timeout' => static::CONNECT_TIMEOUT,
        ];

        $config = array_replace_recursive($defaultConfig, $config);

        if (!is_null($this->redirects)) {
            $config['allow_redirects']['max'] = $this->redirects;
        }

        // TODO: activate this when configuration is allowed
        // return $this->clientService->newClient($config);
        return new Client($config);
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
