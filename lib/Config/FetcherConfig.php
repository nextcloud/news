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
use OCA\News\AppInfo\Application;
use OCA\News\Fetcher\Client\FeedIoClient;
use OCP\IAppConfig;
use OCP\App\IAppManager;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;

/**
 * Class FetcherConfig
 *
 * @package OCA\News\Config
 */
class FetcherConfig
{
    /**
     * Timeout before the client should abort.
     * @var int
     */
    protected readonly int $client_timeout;

    /**
     * Amount of allowed redirects.
     * @var int
     */
    protected readonly int $redirects;

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
     * @param IAppManager $appManager App manager
     * @param IClientService $clientService HTTP client service (provides SSRF protection)
     */
    public function __construct(
        IAppConfig $config,
        IAppManager $appManager,
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
     * Configure a feed-io client backed by Nextcloud's IClientService.
     *
     * The returned client benefits from Nextcloud's built-in SSRF protection
     * and automatic system proxy configuration.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return new FeedIoClient(
            $this->clientService,
            [
                'User-Agent'      => $this->getUserAgent(),
                'Accept'          => static::DEFAULT_ACCEPT,
                'Accept-Encoding' => $this->checkEncoding(),
            ],
            [
                'timeout'         => $this->client_timeout,
                'connect_timeout' => static::CONNECT_TIMEOUT,
                'allow_redirects' => ['max' => $this->redirects, 'referer' => true],
            ]
        );
    }

    /**
     * Return a raw IClient for direct HTTP requests (e.g. favicon downloads).
     *
     * Nextcloud's IClientService applies SSRF protection and honours the
     * system proxy configuration automatically.
     *
     * @return IClient
     */
    public function getHttpClient(): IClient
    {
        return $this->clientService->newClient();
    }

    /**
     * Gets the configured HTTP client timeout in seconds.
     *
     * @return int
     */
    public function getClientTimeout(): int
    {
        return $this->client_timeout;
    }

    /**
     * Gets the configured maximum number of redirects.
     *
     * @return int
     */
    public function getMaxRedirects(): int
    {
        return $this->redirects;
    }

    /**
     * Gets a user agent name for the client
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return 'NextCloud-News/' . $this->version;
    }
}
