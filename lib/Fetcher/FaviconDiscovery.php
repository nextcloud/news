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
use OCA\News\Utility\AppData;
use OCP\Http\Client\IClientService;
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
        $baseUrl = rtrim($baseUrl, '/');
        $client  = $this->clientService->newClient();

        // One page fetch per domain; parse all <link>/<meta> candidates at once.
        $pageHtml = $this->fetchPageHtml($client, $baseUrl . '/');
        $parsed   = $pageHtml !== null
            ? $this->extractCandidatesFromHtml($pageHtml, $baseUrl)
            : ['priority' => [], 'ogImage' => null];

        // Priorities 1–3: apple-touch-icon, sized icon, regular icon.
        foreach ($parsed['priority'] as $candidate) {
            return $candidate;
        }

        // Priority 4: /favicon.ico – HEAD first; download body only on 2xx.
        $faviconIcoUrl = $baseUrl . '/favicon.ico';
        if ($this->headExists($client, $faviconIcoUrl)) {
            return $faviconIcoUrl;
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
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $appleTouchIcons = [];
        $sizedIcons      = [];
        $regularIcons    = [];
        $ogImage         = null;

        foreach ($doc->getElementsByTagName('link') as $link) {
            /** @var \DOMElement $link */
            $rel  = strtolower(trim($link->getAttribute('rel')));
            $href = trim($link->getAttribute('href'));
            if ($href === '') {
                continue;
            }

            if (in_array($rel, ['apple-touch-icon', 'apple-touch-icon-precomposed'], true)) {
                $appleTouchIcons[] = $href;
            } elseif (in_array($rel, ['icon', 'shortcut icon'], true)) {
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
        if (!empty($sizedIcons)) {
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

        // Build priority list (1–3) with at most one candidate per level.
        $priorityCandidates = [];
        if (!empty($appleTouchIcons)) {
            $abs = $this->normaliseUrl(reset($appleTouchIcons), $baseUrl);
            if ($abs !== null) {
                $priorityCandidates[] = $abs;
            }
        }
        if (!empty($sizedIcons)) {
            $abs = $this->normaliseUrl($sizedIcons[0]['href'], $baseUrl);
            if ($abs !== null) {
                $priorityCandidates[] = $abs;
            }
        }
        if (!empty($regularIcons)) {
            $abs = $this->normaliseUrl(reset($regularIcons), $baseUrl);
            if ($abs !== null) {
                $priorityCandidates[] = $abs;
            }
        }

        $ogImageAbs = $ogImage !== null ? $this->normaliseUrl($ogImage, $baseUrl) : null;

        return ['priority' => $priorityCandidates, 'ogImage' => $ogImageAbs];
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
     * @param string $baseUrl  Base URL of the page (without trailing slash).
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
            return $scheme . ':' . $url;
        }

        // Already absolute.
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        // Root-relative path.
        if (str_starts_with($url, '/')) {
            $parts = parse_url($baseUrl);
            if ($parts === false || !isset($parts['host'])) {
                return null;
            }
            $origin = ($parts['scheme'] ?? 'https') . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $origin .= ':' . $parts['port'];
            }
            return $origin . $url;
        }

        // Relative to the base URL.
        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Fetch the homepage HTML, capped at PAGE_BODY_CAP bytes.
     */
    private function fetchPageHtml(mixed $client, string $url): ?string
    {
        try {
            $response = $client->get($url, [
                'timeout'     => 10,
                'http_errors' => false,
                'headers'     => [
                    'User-Agent'      => $this->fetcherConfig->getUserAgent(),
                    'Accept'          => 'text/html',
                    'Accept-Encoding' => $this->fetcherConfig->checkEncoding(),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 400) {
                return null;
            }

            $body = $response->getBody();
            if ($body !== null && strlen($body) > self::PAGE_BODY_CAP) {
                $body = substr($body, 0, self::PAGE_BODY_CAP);
            }
            return $body;
        } catch (\Throwable $e) {
            $this->logger->debug(
                'FaviconDiscovery: could not fetch homepage {url}: {error}',
                ['url' => $url, 'error' => $e->getMessage()]
            );
            return null;
        }
    }

    /**
     * Perform a HEAD request and return true if the response is 2xx.
     */
    private function headExists(mixed $client, string $url): bool
    {
        try {
            $response = $client->head($url, [
                'timeout'     => 10,
                'http_errors' => false,
                'headers'     => [
                    'User-Agent' => $this->fetcherConfig->getUserAgent(),
                ],
            ]);
            $status = $response->getStatusCode();
            return $status >= 200 && $status < 300;
        } catch (\Throwable) {
            return false;
        }
    }
}
