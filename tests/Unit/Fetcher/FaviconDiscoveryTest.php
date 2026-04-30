<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @copyright 2025 Nextcloud GmbH and Nextcloud contributors
 */

namespace OCA\News\Tests\Unit\Fetcher;

use OCA\News\Config\FetcherConfig;
use OCA\News\Constants;
use OCA\News\Fetcher\FaviconDiscovery;
use OCA\News\Utility\AppData;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Security\IRemoteHostValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FaviconDiscoveryTest extends TestCase
{
    private FetcherConfig&MockObject $fetcherConfig;
    private IClientService&MockObject $clientService;
    private IClient&MockObject $client;
    private AppData&MockObject $appData;
    private LoggerInterface&MockObject $logger;
    private IRemoteHostValidator&MockObject $hostValidator;
    private FaviconDiscovery $discovery;
    /** @var \Closure(string, array<string,mixed>): IResponse */
    private \Closure $headCallback;

    protected function setUp(): void
    {
        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');
        $this->fetcherConfig->method('checkEncoding')->willReturn('gzip');
        $this->fetcherConfig->method('getClientTimeout')->willReturn(30);
        $this->fetcherConfig->method('getMaxRedirects')->willReturn(5);

        $this->client = $this->createMock(IClient::class);

        $this->clientService = $this->createMock(IClientService::class);
        $this->clientService->method('newClient')->willReturn($this->client);

        $this->appData = $this->createMock(AppData::class);
        // Default: no cache entry (getFileContent returns null, getMTime returns null by default).
        $this->logger  = $this->createMock(LoggerInterface::class);
        $this->hostValidator = $this->createMock(IRemoteHostValidator::class);
        $this->hostValidator->method('isValid')->willReturn(true);

        $defaultHeadResponse = $this->createMock(IResponse::class);
        $defaultHeadResponse->method('getStatusCode')->willReturn(200);
        $defaultHeadResponse->method('getHeaders')->willReturn([]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $defaultHeadResponse;
        $this->client->method('head')->willReturnCallback(
            fn(string $url, array $opts = []): IResponse => ($this->headCallback)($url, $opts)
        );

        $this->discovery = new FaviconDiscovery(
            $this->fetcherConfig,
            $this->clientService,
            $this->appData,
            $this->logger,
            $this->hostValidator,
        );
    }

    // -------------------------------------------------------------------------
    // Caching
    // -------------------------------------------------------------------------

    public function testReturnsCachedUrlWithoutHttpRequest(): void
    {
        $cacheKey = 'disco_' . md5('https://example.com');

        $this->appData
            ->method('getFileContent')
            ->with(Constants::LOGO_INFO_DIR, $cacheKey)
            ->willReturn('https://example.com/cached.ico');

        // Fresh cache (mtime = now).
        $this->appData
            ->method('getMTime')
            ->with(Constants::LOGO_INFO_DIR, $cacheKey)
            ->willReturn(time());

        $this->client->expects($this->never())->method('get');
        $this->client->expects($this->never())->method('head');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/cached.ico', $result);
    }

    public function testNegativeCacheSentinelReturnsNull(): void
    {
        $cacheKey = 'disco_' . md5('https://example.com');

        $this->appData
            ->method('getFileContent')
            ->willReturn(''); // empty string = negative cache

        // Fresh mtime.
        $this->appData->method('getMTime')->willReturn(time());

        $this->client->expects($this->never())->method('get');

        $result = $this->discovery->discover('https://example.com');

        $this->assertNull($result);
    }

    public function testMalformedUrlReturnsNullWithoutHttpRequest(): void
    {
        $this->client->expects($this->never())->method('get');
        $this->client->expects($this->never())->method('head');

        $this->assertNull($this->discovery->discover(''));
        $this->assertNull($this->discovery->discover('not-a-url'));
        $this->assertNull($this->discovery->discover('ftp://example.com'));
        $this->assertNull($this->discovery->discover('//example.com/no-scheme'));
    }

    public function testExpiredCacheTriggersRediscovery(): void
    {
        $cacheKey = 'disco_' . md5('https://example.com');

        // Return a cached value but with an expired mtime (8 days ago).
        $this->appData->method('getFileContent')->willReturn('https://example.com/old.ico');
        $this->appData->method('getMTime')->willReturn(time() - 8 * 86400);

        // Expect a fresh page fetch.
        $html = '<html><head><link rel="icon" href="/new-icon.png"></head></html>';
        $this->mockPageFetch($html);

        $this->appData->expects($this->once())
            ->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, $cacheKey, 'https://example.com/new-icon.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/new-icon.png', $result);
    }

    public function testWritesNegativeCacheWhenNothingFound(): void
    {
        // No cached value.
        $this->appData->method('getFileContent')->willReturn(null);

        // Homepage fetch returns 404.
        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(404);
        $this->client->method('get')->willReturn($pageResponse);

        // HEAD /favicon.ico returns 404.
        $headResponse404 = $this->createMock(IResponse::class);
        $headResponse404->method('getStatusCode')->willReturn(404);
        $headResponse404->method('getHeaders')->willReturn([]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $headResponse404;

        // Expect negative-cache sentinel to be written.
        $this->appData
            ->expects($this->once())
            ->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), '');

        $result = $this->discovery->discover('https://example.com');

        $this->assertNull($result);
    }

    public function testWritesDiscoveredUrlToCache(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="icon" href="/icon.png"></head></html>';
        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn($html);
        $this->client->method('get')->willReturn($pageResponse);

        $this->appData
            ->expects($this->once())
            ->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/icon.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon.png', $result);
    }

    public function testCanonicalisedBaseUrlUsesSameCacheKeyWithTrailingSlash(): void
    {
        $cacheKey = 'disco_' . md5('https://example.com');
        $readCount = 0;

        $this->appData
            ->expects($this->exactly(2))
            ->method('getFileContent')
            ->willReturnCallback(function (string $folder, string $key) use ($cacheKey, &$readCount) {
                $this->assertSame(Constants::LOGO_INFO_DIR, $folder);
                $this->assertSame($cacheKey, $key);
                $readCount++;

                return $readCount === 1 ? null : 'https://example.com/icon.png';
            });

        $this->appData
            ->expects($this->once())
            ->method('getMTime')
            ->with(Constants::LOGO_INFO_DIR, $cacheKey)
            ->willReturn(time());

        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn('<html><head><link rel="icon" href="/icon.png"></head></html>');

        $this->client->expects($this->once())
            ->method('get')
            ->willReturn($pageResponse);

        $this->appData
            ->expects($this->once())
            ->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, $cacheKey, 'https://example.com/icon.png');

        $resultWithSlash = $this->discovery->discover('https://example.com/');
        $resultWithoutSlash = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon.png', $resultWithSlash);
        $this->assertSame('https://example.com/icon.png', $resultWithoutSlash);
    }

    public function testCanonicalisedBaseUrlIgnoresPathAndQuery(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="/icon.png"></head></html>';
        $this->mockPageFetch($html);

        $result = $this->discovery->discover('https://example.com/blog/index.html?ref=1');

        $this->assertSame('https://example.com/icon.png', $result);
    }

    // -------------------------------------------------------------------------
    // Priority 1: apple-touch-icon
    // -------------------------------------------------------------------------

    public function testAppleTouchIconTakesPriorityOverOtherLinks(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = <<<HTML
        <html><head>
          <link rel="apple-touch-icon" href="/apple-touch-icon.png">
          <link rel="icon" sizes="32x32" href="/icon-32.png">
          <link rel="shortcut icon" href="/favicon.ico">
        </head></html>
        HTML;

        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/apple-touch-icon.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/apple-touch-icon.png', $result);
    }

    public function testAppleTouchIconPrecomposedIsAccepted(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="apple-touch-icon-precomposed" href="/touch.png"></head></html>';
        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/touch.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/touch.png', $result);
    }

    // -------------------------------------------------------------------------
    // Priority 2: sized icons
    // -------------------------------------------------------------------------

    public function testLargestSizedIconIsPreferred(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = <<<HTML
        <html><head>
          <link rel="icon" sizes="16x16" href="/icon-16.png">
          <link rel="icon" sizes="64x64" href="/icon-64.png">
          <link rel="icon" sizes="32x32" href="/icon-32.png">
        </head></html>
        HTML;

        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/icon-64.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon-64.png', $result);
    }

    public function testSvgPreferredOverRasterWhenSizesEqual(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = <<<HTML
        <html><head>
          <link rel="icon" sizes="32x32" href="/icon-32.png" type="image/png">
          <link rel="icon" sizes="32x32" href="/icon.svg" type="image/svg+xml">
        </head></html>
        HTML;

        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/icon.svg');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon.svg', $result);
    }

    public function testSvgSizesAnyCountsAsScalable(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = <<<HTML
        <html><head>
          <link rel="icon" sizes="any" href="/icon.svg" type="image/svg+xml">
          <link rel="icon" sizes="256x256" href="/icon-256.png">
        </head></html>
        HTML;

        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/icon.svg');

        $result = $this->discovery->discover('https://example.com');

        // "any" maps to PHP_INT_MAX dimension so SVG wins.
        $this->assertSame('https://example.com/icon.svg', $result);
    }

    // -------------------------------------------------------------------------
    // Priority 3: regular icon (no sizes)
    // -------------------------------------------------------------------------

    public function testRegularIconWithoutSizesIsUsedAsFallback(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="shortcut icon" href="/favicon.png"></head></html>';
        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/favicon.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.png', $result);
    }

    public function testRegularIconSupportsRelTokenOrderVariants(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="icon shortcut" href="/favicon-order.png"></head></html>';
        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/favicon-order.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon-order.png', $result);
    }

    public function testRegularIconSupportsRelWithAdditionalTokens(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="shortcut icon alternate" href="/favicon-extra-token.png"></head></html>';
        $this->mockPageFetch($html);
        $this->appData->expects($this->once())->method('putFileContent')
            ->with(Constants::LOGO_INFO_DIR, 'disco_' . md5('https://example.com'), 'https://example.com/favicon-extra-token.png');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon-extra-token.png', $result);
    }

    // -------------------------------------------------------------------------
    // Priority 4: /favicon.ico fallback
    // -------------------------------------------------------------------------

    public function testHtmlCandidateReturnsRedirectResolvedEffectiveUrl(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="/icon.png"></head></html>';
        $this->mockPageFetch($html);

        $headResponseRedirect = $this->createMock(IResponse::class);
        $headResponseRedirect->method('getStatusCode')->willReturn(200);
        $headResponseRedirect->method('getHeaders')->willReturn([
            'X-Guzzle-Redirect-History' => ['https://cdn.example.com/icon-final.png'],
        ]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $headResponseRedirect;

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://cdn.example.com/icon-final.png', $result);
    }

    public function testUnreachableHtmlCandidateFallsBackToFaviconIco(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="/broken.png"></head></html>';
        $this->mockPageFetch($html);

        $candidateHead = $this->createMock(IResponse::class);
        $candidateHead->method('getStatusCode')->willReturn(404);
        $candidateHead->method('getHeaders')->willReturn([]);

        $fallbackHead = $this->createMock(IResponse::class);
        $fallbackHead->method('getStatusCode')->willReturn(200);
        $fallbackHead->method('getHeaders')->willReturn([]);

        $this->headCallback = function (string $url) use ($candidateHead, $fallbackHead): IResponse {
            if ($url === 'https://example.com/broken.png') {
                return $candidateHead;
            }
            if ($url === 'https://example.com/favicon.ico') {
                return $fallbackHead;
            }

            throw new \RuntimeException('Unexpected HEAD URL ' . $url);
        };

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    public function testFaviconIcoFallbackUsedWhenNoHtmlLinks(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Page returns HTML with no icon links.
        $html = '<html><head><title>Hello</title></head></html>';
        $this->mockPageFetch($html);

        // HEAD /favicon.ico returns 200 (default setUp headCallback is used).

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    public function testFaviconIcoFallbackReturnsFinalRedirectUrl(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><title>Hello</title></head></html>';
        $this->mockPageFetch($html);

        $headResponseRedirect = $this->createMock(IResponse::class);
        $headResponseRedirect->method('getStatusCode')->willReturn(200);
        $headResponseRedirect->method('getHeaders')->willReturn([
            'X-Guzzle-Redirect-History' => ['https://cdn.example.com/assets/favicon.ico'],
        ]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $headResponseRedirect;

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://cdn.example.com/assets/favicon.ico', $result);
    }

    public function testFaviconIcoSkippedWhenHeadReturnsNon2xx(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Page has no icon links.
        $html = '<html><head></head></html>';
        $this->mockPageFetch($html);

        // HEAD returns 404.
        $headResponse404 = $this->createMock(IResponse::class);
        $headResponse404->method('getStatusCode')->willReturn(404);
        $headResponse404->method('getHeaders')->willReturn([]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $headResponse404;

        $result = $this->discovery->discover('https://example.com');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Priority 5: og:image
    // -------------------------------------------------------------------------

    public function testOgImageUsedAsLastResort(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = <<<HTML
        <html><head>
          <meta property="og:image" content="https://example.com/og.jpg">
        </head></html>
        HTML;
        $this->mockPageFetch($html);

        // HEAD /favicon.ico returns 404 so og:image is the final fallback.
        $headResponse404 = $this->createMock(IResponse::class);
        $headResponse404->method('getStatusCode')->willReturn(404);
        $headResponse404->method('getHeaders')->willReturn([]);
        $this->headCallback = fn(string $url, array $opts): IResponse => $headResponse404;

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/og.jpg', $result);
    }

    public function testFaviconIcoTakesPriorityOverOgImage(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><meta property="og:image" content="https://example.com/og.jpg"></head></html>';
        $this->mockPageFetch($html);

        // HEAD /favicon.ico succeeds (default setUp headCallback returns 200).

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    // -------------------------------------------------------------------------
    // URL normalisation
    // -------------------------------------------------------------------------

    public function testRelativeUrlIsNormalisedToAbsolute(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="icon.png"></head></html>';
        $this->mockPageFetch($html);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon.png', $result);
    }

    public function testRelativeUrlIsResolvedAgainstFinalRedirectUrl(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="icon.png"></head></html>';
        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn($html);
        $pageResponse->method('getHeaders')->willReturn([
            'X-Guzzle-Redirect-History' => ['https://www.example.com/news/home/index.html'],
        ]);

        $this->client->method('get')->willReturn($pageResponse);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://www.example.com/news/home/icon.png', $result);
    }

    public function testRootRelativeUrlIsNormalisedToAbsolute(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="/assets/icon.png"></head></html>';
        $this->mockPageFetch($html);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/assets/icon.png', $result);
    }

    public function testProtocolRelativeUrlInheritsScheme(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="//cdn.example.com/icon.png"></head></html>';
        $this->mockPageFetch($html);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://cdn.example.com/icon.png', $result);
    }

    public function testAlreadyAbsoluteUrlIsPassedThrough(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><link rel="icon" href="https://cdn.example.com/icon.png"></head></html>';
        $this->mockPageFetch($html);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://cdn.example.com/icon.png', $result);
    }

    // -------------------------------------------------------------------------
    // 500 KB page-body cap
    // -------------------------------------------------------------------------

    public function testPageBodyCapTruncatesLargeResponse(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Build a body that is larger than 500 KB and has the icon link near
        // the start so it can still be found after truncation.
        $iconHtml  = '<html><head><link rel="icon" href="/icon.png">';
        $padding   = str_repeat('x', 600_000); // 600 KB of padding
        $fullHtml  = $iconHtml . $padding . '</head></html>';

        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn($fullHtml);
        $this->client->method('get')->willReturn($pageResponse);

        $result = $this->discovery->discover('https://example.com');

        // Should still find the icon (it's in the first 500 KB).
        $this->assertSame('https://example.com/icon.png', $result);
    }

    public function testHomepageRequestUsesConfiguredTimeoutRedirectTrackingAndRange(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn('<html><head><link rel="icon" href="/icon.png"></head></html>');
        $pageResponse->method('getHeaders')->willReturn([]);

        $this->client->expects($this->once())
            ->method('get')
            ->with(
                'https://example.com/',
                $this->callback(function (array $options): bool {
                    $this->assertSame(30, $options['timeout']);
                    $this->assertFalse($options['http_errors']);
                    $this->assertSame(5, $options['allow_redirects']['max']);
                    $this->assertTrue($options['allow_redirects']['track_redirects']);
                    $this->assertSame('bytes=0-511999', $options['headers']['Range']);
                    $this->assertSame('text/html', $options['headers']['Accept']);

                    return is_callable($options['allow_redirects']['on_redirect']);
                })
            )
            ->willReturn($pageResponse);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/icon.png', $result);
    }

    public function testHeadRequestUsesConfiguredTimeoutAndRedirectTracking(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $this->mockPageFetch('<html><head></head></html>');

        $capturedOpts = [];
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(200);
        $headResponse->method('getHeaders')->willReturn([]);
        $this->headCallback = function (string $url, array $opts) use ($headResponse, &$capturedOpts): IResponse {
            $capturedOpts = $opts;
            return $headResponse;
        };

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
        $this->assertSame(30, $capturedOpts['timeout']);
        $this->assertFalse($capturedOpts['http_errors']);
        $this->assertSame(5, $capturedOpts['allow_redirects']['max']);
        $this->assertTrue($capturedOpts['allow_redirects']['track_redirects']);
        $this->assertArrayNotHasKey('Range', $capturedOpts['headers']);
        $this->assertTrue(is_callable($capturedOpts['allow_redirects']['on_redirect']));
    }

    // -------------------------------------------------------------------------
    // HTTP error handling
    // -------------------------------------------------------------------------

    public function testHomepageFetchFailureDoesNotBreakDiscovery(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // GET throws an exception (network error).
        $this->client->method('get')->willThrowException(new \Exception('Connection refused'));

        // HEAD /favicon.ico succeeds (default setUp headCallback returns 200).

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    public function testHeadThrowingExceptionIsTreatedAsNotFound(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head></head></html>';
        $this->mockPageFetch($html);

        $this->headCallback = function (string $url, array $opts): IResponse {
            throw new \Exception('Timeout');
        };

        $result = $this->discovery->discover('https://example.com');

        $this->assertNull($result);
    }

    public function testSsrfRedirectIsRejectedDuringHeadProbe(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Page has no icon links so we fall through to /favicon.ico.
        $this->mockPageFetch('<html><head></head></html>');

        // Simulate the on_redirect closure rejecting an internal redirect.
        $this->hostValidator->method('isValid')->willReturnCallback(
            fn(string $host): bool => !in_array($host, ['localhost', '127.0.0.1', '192.168.0.1'], true)
        );

        // HEAD throws LocalServerException (what on_redirect raises) when
        // the redirect destination is a private host.
        $this->headCallback = function (string $url, array $opts): IResponse {
            // Invoke the on_redirect closure with a forbidden URI to verify it
            // is actually wired and raises LocalServerException.
            $forbiddenUri = new class { public function __toString(): string { return 'http://192.168.0.1/icon.ico'; } };
            ($opts['allow_redirects']['on_redirect'])(null, null, $forbiddenUri);
            // Should never reach here.
            throw new \LogicException('on_redirect should have thrown');
        };

        $result = $this->discovery->discover('https://example.com');

        // headExists catches LocalServerException as \Throwable so the probe
        // fails gracefully and the whole discovery returns null.
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function mockPageFetch(string $html): void
    {
        $pageResponse = $this->createMock(IResponse::class);
        $pageResponse->method('getStatusCode')->willReturn(200);
        $pageResponse->method('getBody')->willReturn($html);
        $pageResponse->method('getHeaders')->willReturn([]);
        $this->client->method('get')->willReturn($pageResponse);
    }
}
