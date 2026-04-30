<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Nextcloud GmbH and Nextcloud contributors
 * @copyright 2025 Nextcloud GmbH
 */

namespace OCA\News\Fetcher;

use OCA\News\Config\FetcherConfig;
use OCA\News\Constants;
use OCA\News\Vendor\GuzzleHttp\Psr7\Uri;
use OCA\News\Vendor\GuzzleHttp\Psr7\UriResolver;
use OCA\News\Utility\AppData;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Http\Client\LocalServerException;
use OCP\Security\IRemoteHostValidator;
use Psr\Log\LoggerInterface;

/**
 * In-source favicon discovery service.
 *
 * Replaces the arthurhoaro/favicon library with a self-contained
 * implementation that:
 *  - Uses Nextcloud's IClientService for SSRF-safe HTTP requests.
 *  - Caches discovered URLs in Nextcloud AppData (Layer A).
 *  - Follows the priority order specified in issue #3695.
 */
class FaviconDiscovery
{
    /** TTL for the discovery cache (7 days). */
    private const CACHE_TTL = 7 * 86400;

    /** Maximum bytes to read from the homepage before parsing (500 KB). */
    private const PAGE_BODY_CAP = 512000;

    public function __construct(
        private readonly FetcherConfig $fetcherConfig,
        private readonly IClientService $clientService,
        private readonly AppData $appData,
        private readonly LoggerInterface $logger,
        private readonly IRemoteHostValidator $hostValidator,
    ) {
    }

    /**
     * Discover the best favicon URL for the given site base URL.
     *
     * Results are cached in AppData (key: disco_<md5(baseUrl)>) for 7 days.
     * An empty-string cache entry acts as a negative-cache sentinel so that
     * domains with no favicon are not re-probed on every feed update.
     *
     * @param string $baseUrl The base URL of the site (e.g. "https://example.com/")
     * @return string|null The resolved candidate URL, or null if nothing was found.
     */
    public function discover(string $baseUrl): ?string
    {
        $baseUrl = $this->normaliseBaseUrl($baseUrl);
        if ($baseUrl === null) {
            return null;
        }

        $cacheKey = 'disco_' . md5($baseUrl);

        $cached = $this->appData->getFileContent(Constants::LOGO_INFO_DIR, $cacheKey);
        if ($cached !== null) {
            // Honour 7-day TTL; re-discover after expiry.
            $mtime = $this->appData->getMTime(Constants::LOGO_INFO_DIR, $cacheKey);
            if ($mtime !== null && (time() - $mtime) < self::CACHE_TTL) {
                return $cached === '' ? null : $cached;
            }
        }

        $result = $this->doDiscover($baseUrl);

        // Store result; empty string = negative-cache sentinel.
        $this->appData->putFileContent(Constants::LOGO_INFO_DIR, $cacheKey, $result ?? '');

        return $result;
    }

    // -------------------------------------------------------------------------
    // Internal discovery logic
    // -------------------------------------------------------------------------

    private function doDiscover(string $baseUrl): ?string
    {
        $client  = $this->clientService->newClient();

        // One page fetch per domain; parse all <link>/<meta> candidates at once.
        $page = $this->fetchPageHtml($client, $baseUrl . '/');
        $pageUrl = $page !== null
            ? ($page['effectiveUrl'] ?? ($baseUrl . '/'))
            : ($baseUrl . '/');
        $siteUrl = $this->normaliseBaseUrl($pageUrl) ?? $baseUrl;

        $parsed = $page !== null
            ? $this->extractCandidatesFromHtml($page['body'] ?? '', $pageUrl)
            : ['priority' => [], 'ogImage' => null];

        // Priorities 1–3: apple-touch-icon, sized icon, regular icon.
        // Probe each candidate with HEAD to validate reachability and capture
        // the final redirect-resolved URL before caching.
        foreach ($parsed['priority'] as $candidate) {
            $effectiveCandidateUrl = $candidate;
            if ($this->headExists($client, $candidate, $effectiveCandidateUrl)) {
                return $effectiveCandidateUrl;
            }
        }

        // Priority 4: /favicon.ico – HEAD first; download body only on 2xx.
        $faviconIcoUrl = $siteUrl . '/favicon.ico';
        $effectiveFaviconUrl = $faviconIcoUrl;
        if ($this->headExists($client, $faviconIcoUrl, $effectiveFaviconUrl)) {
            return $effectiveFaviconUrl;
        }

        // Priority 5: og:image – last resort.
        if ($parsed['ogImage'] !== null) {
            return $parsed['ogImage'];
        }

        return null;
    }

    /**
     * Extract favicon candidates from HTML in priority order.
     *
     * Priority:
     *  1. apple-touch-icon / apple-touch-icon-precomposed
     *  2. <link rel="icon" sizes="…"> – largest dimension, SVG preferred on tie
     *  3. <link rel="shortcut icon"> or <link rel="icon"> without sizes
     *  4. (handled outside: /favicon.ico)
     *  5. <meta property="og:image">
     *
     * @return array{priority: string[], ogImage: string|null}
     *   'priority' contains absolute URLs for priorities 1–3 in order;
     *   'ogImage'  is the og:image URL or null.
     */
    private function extractCandidatesFromHtml(string $html, string $baseUrl): array
    {
        $previousErrorMode = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();

        try {
            $doc->loadHTML('<?xml encoding="UTF-8">' . $html);

            $appleTouchIcons = [];
            $sizedIcons      = [];
            $regularIcons    = [];
            $ogImage         = null;

            foreach ($doc->getElementsByTagName('link') as $link) {
                /** @var \DOMElement $link */
                $relValue = strtolower(trim($link->getAttribute('rel')));
                $relTokens = preg_split('/\\s+/', $relValue, -1, PREG_SPLIT_NO_EMPTY);
                $relTokens = $relTokens !== false ? $relTokens : [];
                $href = trim($link->getAttribute('href'));
                if ($href === '') {
                    continue;
                }

                $isAppleTouchIcon = in_array('apple-touch-icon', $relTokens, true)
                    || in_array('apple-touch-icon-precomposed', $relTokens, true);
                $isIcon = in_array('icon', $relTokens, true);

                if ($isAppleTouchIcon) {
                    $appleTouchIcons[] = $href;
                } elseif ($isIcon) {
                    $sizes = trim($link->getAttribute('sizes'));
                    if ($sizes !== '') {
                        $type = strtolower(trim($link->getAttribute('type')));
                        $sizedIcons[] = [
                            'href' => $href,
                            'dim'  => $this->parseLargestDimension($sizes),
                            'type' => $type,
                        ];
                    } else {
                        $regularIcons[] = $href;
                    }
                }
            }

            foreach ($doc->getElementsByTagName('meta') as $meta) {
                /** @var \DOMElement $meta */
                if (strtolower(trim($meta->getAttribute('property'))) === 'og:image') {
                    $content = trim($meta->getAttribute('content'));
                    if ($content !== '') {
                        $ogImage = $content;
                        break; // First og:image wins.
                    }
                }
            }

            // Sort sized icons: largest first; SVG preferred over raster on tie.
            if ($sizedIcons !== []) {
                usort($sizedIcons, static function (array $a, array $b): int {
                    $isSvgA = ($a['type'] === 'image/svg+xml');
                    $isSvgB = ($b['type'] === 'image/svg+xml');
                    if ($a['dim'] === $b['dim']) {
                        if ($isSvgA && !$isSvgB) {
                            return -1;
                        }
                        if ($isSvgB && !$isSvgA) {
                            return 1;
                        }
                        return 0;
                    }
                    return $b['dim'] <=> $a['dim'];
                });
            }

            // Build priority list (1-3): all candidates per tier so that a
            // 404 on the first candidate falls back to the next in the same
            // tier before dropping to a lower-priority tier.
            $priorityCandidates = [];
            foreach ($appleTouchIcons as $href) {
                $abs = $this->normaliseUrl($href, $baseUrl);
                if ($abs !== null) {
                    $priorityCandidates[] = $abs;
                }
            }
            foreach ($sizedIcons as $icon) {
                $abs = $this->normaliseUrl($icon['href'], $baseUrl);
                if ($abs !== null) {
                    $priorityCandidates[] = $abs;
                }
            }
            foreach ($regularIcons as $href) {
                $abs = $this->normaliseUrl($href, $baseUrl);
                if ($abs !== null) {
                    $priorityCandidates[] = $abs;
                }
            }

            $ogImageAbs = $ogImage !== null ? $this->normaliseUrl($ogImage, $baseUrl) : null;

            return ['priority' => $priorityCandidates, 'ogImage' => $ogImageAbs];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }
    }

    /**
     * Parse the largest pixel dimension from an HTML sizes attribute value.
     *
     * @param string $sizes e.g. "32x32", "16x16 32x32", "any"
     * @return int
     */
    private function parseLargestDimension(string $sizes): int
    {
        $max = 0;
        foreach (explode(' ', strtolower($sizes)) as $token) {
            if ($token === 'any') {
                return PHP_INT_MAX; // scalable (SVG)
            }
            if (preg_match('/(\d+)x(\d+)/', $token, $m)) {
                $max = max($max, (int)$m[1], (int)$m[2]);
            }
        }
        return $max;
    }

    /**
     * Normalise a raw href/src to an absolute URL.
     *
     * @param string $url      Raw value from the HTML attribute.
     * @param string $baseUrl  Base URL of the page.
     * @return string|null     Absolute URL, or null if it cannot be resolved.
     */
    private function normaliseUrl(string $url, string $baseUrl): ?string
    {
        if ($url === '') {
            return null;
        }

        // Protocol-relative.
        if (str_starts_with($url, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https';
            $resolvedUrl = $scheme . ':' . $url;

            return $this->isHttpUrl($resolvedUrl) ? $resolvedUrl : null;
        }

        // Already absolute.
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $this->isHttpUrl($url) ? $url : null;
        }

        try {
            $resolvedUrl = (string) UriResolver::resolve(new Uri($baseUrl), new Uri($url));

            return $this->isHttpUrl($resolvedUrl) ? $resolvedUrl : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function isHttpUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!is_string($scheme)) {
            return false;
        }

        $scheme = strtolower($scheme);

        return $scheme === 'http' || $scheme === 'https';
    }
    private function normaliseBaseUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $baseUrl = $scheme . '://' . $parts['host'];
        if (isset($parts['port'])) {
            $baseUrl .= ':' . $parts['port'];
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Fetch the homepage HTML, capped at PAGE_BODY_CAP bytes.
     *
     * @return array{body: string|null, effectiveUrl: string}|null
     */
    private function fetchPageHtml(mixed $client, string $url): ?array
    {
        try {
            $response = $client->get($url, [
                ...$this->getHttpOptions(),
                'headers' => [
                    'User-Agent'      => $this->fetcherConfig->getUserAgent(),
                    'Accept'          => 'text/html',
                    'Accept-Encoding' => $this->fetcherConfig->checkEncoding(),
                    'Range'           => 'bytes=0-' . (self::PAGE_BODY_CAP - 1),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                return null;
            }

            $body = $response->getBody();
            if ($body !== null && strlen($body) > self::PAGE_BODY_CAP) {
                $body = substr($body, 0, self::PAGE_BODY_CAP);
            }

            return [
                'body' => $body,
                'effectiveUrl' => $this->extractEffectiveUrl($response, $url),
            ];
        } catch (\Throwable $e) {
            $this->logger->debug(
                'FaviconDiscovery: could not fetch homepage {url}: {error}',
                ['url' => $url, 'error' => $e->getMessage()]
            );
            return null;
        }
    }

    /**
     * Perform a HEAD request and return true if the final response is 2xx.
     */
    private function headExists(mixed $client, string $url, ?string &$effectiveUrl = null): bool
    {
        $effectiveUrl = $url;

        try {
            $response = $client->head($url, $this->getHttpOptions());
            $effectiveUrl = $this->extractEffectiveUrl($response, $url);
            $status = $response->getStatusCode();
            return $status >= 200 && $status < 300;
        } catch (\Throwable) {
            $effectiveUrl = $url;
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getHttpOptions(): array
    {
        return [
            'timeout' => $this->fetcherConfig->getClientTimeout(),
            'http_errors' => false,
            'allow_redirects' => [
                'referer' => true,
                'track_redirects' => true,
                'max' => $this->fetcherConfig->getMaxRedirects(),
                'on_redirect' => function ($request, $response, $uri): void {
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
            'headers' => [
                'User-Agent' => $this->fetcherConfig->getUserAgent(),
            ],
        ];
    }

    private function extractEffectiveUrl(IResponse $response, string $fallbackUrl): string
    {
        $headers = $response->getHeaders();
        if (!is_array($headers)) {
            return $fallbackUrl;
        }

        $headers = array_change_key_case($headers, CASE_LOWER);
        $history = $headers['x-guzzle-redirect-history'] ?? [];
        if (is_string($history)) {
            $history = [$history];
        }

        if (is_array($history) && $history !== []) {
            $redirectUrl = end($history);
            if (is_string($redirectUrl) && $redirectUrl !== '') {
                return $redirectUrl;
            }
        }

        return $fallbackUrl;
    }
}
