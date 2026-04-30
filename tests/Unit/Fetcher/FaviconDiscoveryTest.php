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
    private FaviconDiscovery $discovery;

    protected function setUp(): void
    {
        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');
        $this->fetcherConfig->method('checkEncoding')->willReturn('gzip');

        $this->client = $this->createMock(IClient::class);

        $this->clientService = $this->createMock(IClientService::class);
        $this->clientService->method('newClient')->willReturn($this->client);

        $this->appData = $this->createMock(AppData::class);
        // Default: no cache entry (getFileContent returns null, getMTime returns null by default).
        $this->logger  = $this->createMock(LoggerInterface::class);

        $this->discovery = new FaviconDiscovery(
            $this->fetcherConfig,
            $this->clientService,
            $this->appData,
            $this->logger,
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
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(404);
        $this->client->method('head')->willReturn($headResponse);

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
        $this->appData->method('putFileContent');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/apple-touch-icon.png', $result);
    }

    public function testAppleTouchIconPrecomposedIsAccepted(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);

        $html = '<html><head><link rel="apple-touch-icon-precomposed" href="/touch.png"></head></html>';
        $this->mockPageFetch($html);
        $this->appData->method('putFileContent');

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
        $this->appData->method('putFileContent');

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
        $this->appData->method('putFileContent');

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
        $this->appData->method('putFileContent');

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
        $this->appData->method('putFileContent');

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.png', $result);
    }

    // -------------------------------------------------------------------------
    // Priority 4: /favicon.ico fallback
    // -------------------------------------------------------------------------

    public function testFaviconIcoFallbackUsedWhenNoHtmlLinks(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Page returns HTML with no icon links.
        $html = '<html><head><title>Hello</title></head></html>';
        $this->mockPageFetch($html);

        // HEAD /favicon.ico returns 200.
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(200);
        $this->client->method('head')->willReturn($headResponse);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    public function testFaviconIcoSkippedWhenHeadReturnsNon2xx(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // Page has no icon links.
        $html = '<html><head></head></html>';
        $this->mockPageFetch($html);

        // HEAD returns 404.
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(404);
        $this->client->method('head')->willReturn($headResponse);

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
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(404);
        $this->client->method('head')->willReturn($headResponse);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/og.jpg', $result);
    }

    public function testFaviconIcoTakesPriorityOverOgImage(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head><meta property="og:image" content="https://example.com/og.jpg"></head></html>';
        $this->mockPageFetch($html);

        // HEAD /favicon.ico succeeds.
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(200);
        $this->client->method('head')->willReturn($headResponse);

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

    // -------------------------------------------------------------------------
    // HTTP error handling
    // -------------------------------------------------------------------------

    public function testHomepageFetchFailureDoesNotBreakDiscovery(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        // GET throws an exception (network error).
        $this->client->method('get')->willThrowException(new \Exception('Connection refused'));

        // HEAD /favicon.ico succeeds.
        $headResponse = $this->createMock(IResponse::class);
        $headResponse->method('getStatusCode')->willReturn(200);
        $this->client->method('head')->willReturn($headResponse);

        $result = $this->discovery->discover('https://example.com');

        $this->assertSame('https://example.com/favicon.ico', $result);
    }

    public function testHeadThrowingExceptionIsTreatedAsNotFound(): void
    {
        $this->appData->method('getFileContent')->willReturn(null);
        $this->appData->method('putFileContent');

        $html = '<html><head></head></html>';
        $this->mockPageFetch($html);

        $this->client->method('head')->willThrowException(new \Exception('Timeout'));

        $result = $this->discovery->discover('https://example.com');

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
        $this->client->method('get')->willReturn($pageResponse);
    }
}
