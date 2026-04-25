<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Gioele Falcetti <thegio.f@gmail.com>
 * @copyright 2019 Gioele Falcetti
 */

namespace OCA\News\Scraper;

use OCA\News\Vendor\fivefilters\Readability\Readability;
use OCA\News\Vendor\fivefilters\Readability\Configuration;
use OCA\News\Vendor\fivefilters\Readability\ParseException;
use OCA\News\Vendor\League\Uri\Exceptions\SyntaxError;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\LocalServerException;
use OCP\Security\IRemoteHostValidator;
use Psr\Log\LoggerInterface;
use OCA\News\Config\FetcherConfig;

class Scraper implements IScraper
{
    private $config;
    private $readability;

    // Cached list of supported mbstring encodings
    private static $supportedEncodingList = null;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FetcherConfig $fetcherConfig,
        private readonly IClientService $clientService,
        private readonly IRemoteHostValidator $hostValidator
    ) {
        $this->config = new Configuration([
            'FixRelativeURLs' => true,
            'SummonCthulhu' => true, // Remove <script>
        ]);
        $this->readability = null;

        if (self::$supportedEncodingList === null) {
            self::$supportedEncodingList = mb_list_encodings();
        }
    }

    private function getHTTPContent(string $url): array
    {
        $effectiveUrl = $url;
        try {
            $response = $this->clientService->newClient()->get($url, [
                'timeout'     => $this->fetcherConfig->getClientTimeout(),
                'http_errors' => false,
                'headers'     => [
                    'User-Agent' => $this->fetcherConfig->getUserAgent(),
                ],
                // track_redirects populates X-Guzzle-Redirect-History in the response
                // so we can determine the effective URL after all redirects are followed.
                // We replicate Nextcloud's SSRF on_redirect check here because passing a
                // custom allow_redirects array overrides the IClientService default that
                // would otherwise inject an SSRF-checking on_redirect callback.
                'allow_redirects' => [
                    'referer'         => true,
                    'track_redirects' => true,
                    'max'             => $this->fetcherConfig->getMaxRedirects(),
                    'on_redirect'     => function ($request, $response, $uri): void {
                        $host = parse_url((string) $uri, PHP_URL_HOST);
                        if ($host === false || $host === null) {
                            throw new LocalServerException('Could not determine host for redirect destination');
                        }
                        if (!$this->hostValidator->isValid($host)) {
                            throw new LocalServerException(
                                'Redirect destination "' . $host . '" violates local access rules'
                            );
                        }
                    },
                ],
            ]);

            // Guzzle's RedirectMiddleware stores each redirect destination in
            // X-Guzzle-Redirect-History. Normalize to an array because IResponse
            // may return the header as either a string or an array depending on
            // implementation. The effective URL is the LAST recorded destination.
            $headers = array_change_key_case($response->getHeaders(), CASE_LOWER);
            $history = $headers['x-guzzle-redirect-history'] ?? [];
            if (is_string($history)) {
                $history = [$history];
            }
            if ($history !== []) {
                $effectiveUrl = end($history);
            }

            // Treat non-2xx responses as fetch failures so error pages aren't scraped.
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                return array(null, null);
            }

            $content     = $response->getBody();
            $contentType = $response->getHeader('Content-Type');

            $charset = null;
            // check if charset is set in http header
            if ($contentType &&
                preg_match('/charset\s*=\s*"?([\w\-]+)"?/i', $contentType, $m)) {
                $charset = strtoupper($m[1]);
            }
            // search content for meta tag with charset
            if ($charset === null &&
                preg_match('/<meta[^>]+charset\s*=\s*["\']?([\w\-]+)["\']?[^>]+>/i', $content, $m)) {
                $charset = strtoupper($m[1]);
            }
            // invalidate unsupported charsets to get the chance that a supported alias is detected
            if ($charset !== null &&
                !in_array($charset, self::$supportedEncodingList, true)) {
                $this->logger->debug(
                    'Ignoring unsupported charset {charset} from full text feed item',
                    ['charset' => $charset]
                );
                $charset = null;
            }
            // try to detect encoding
            if ($charset === null) {
                $encodingList = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII', 'UTF-16', 'UTF-16BE', 'UTF-16LE'];
                $charset = mb_detect_encoding($content, $encodingList, true);
                if ($charset === false) {
                    $charset = null;
                }
            }
            // convert to utf-8 if necessary
            if ($charset !== null && $charset !== 'UTF-8') {
                $convertedContent = mb_convert_encoding($content, 'UTF-8', $charset);
                if ($convertedContent !== false) {
                    $content = $convertedContent;
                } else {
                    $this->logger->warning(
                        'Failed to convert encoding from {from} to UTF-8 for full text feed item',
                        ['from' => $charset]
                    );
                }
            }

            // Update the url after the redirects has been followed
            return array($content, $effectiveUrl);
        } catch (\Throwable $e) {
            $this->logger->debug('Error fetching {url} request returned {error}', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            return array(null, null);
        }
    }

    public function scrape(string $url): bool
    {
        list($content, $redirected_url) = $this->getHTTPContent($url);
        if (is_null($content)) {
            $this->logger->error('Unable to receive content from {url}', [
                 'url' => $url,
            ]);
            $this->readability = null;
            return false;
        }

        // Update URL used to convert relative URLs
        $this->config->setOriginalURL($redirected_url);
        $this->readability = new Readability($this->config);

        try {
            $this->readability->parse($content);
        } catch (ParseException | SyntaxError $e) {
            $this->logger->error('Unable to parse content from {url}', [
                 'url' => $url,
            ]);
            $this->logger->debug('Error during parsing of {url} ran into {error}', [
                'url'   => $url,
                'error' => $e,
            ]);
        }
        return true;
    }

    public function getContent(): ?string
    {
        if ($this->readability === null) {
            return null;
        }
        return $this->readability->getContent();
    }

    public function getRTL(bool $default = false): bool
    {
        if ($this->readability === null) {
            return $default;
        }

        $RTL = $this->readability->getDirection();
        if ($RTL === null) {
            return $default;
        }
        return $RTL === "rtl";
    }
}
