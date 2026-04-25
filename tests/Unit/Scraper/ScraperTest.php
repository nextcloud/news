<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @copyright 2026 Nextcloud GmbH and Nextcloud contributors
 */

namespace OCA\News\Tests\Unit\Scraper;

use PHPUnit\Framework\TestCase;
use OCA\News\Scraper\Scraper;
use OCA\News\Config\FetcherConfig;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCP\Security\IRemoteHostValidator;
use Psr\Log\LoggerInterface;

class ScraperTest extends TestCase
{
    private LoggerInterface $logger;
    private FetcherConfig $fetcherConfig;
    private IClient $client;
    private IClientService $clientService;
    private IRemoteHostValidator $hostValidator;
    private Scraper $scraper;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->client = $this->createMock(IClient::class);

        $this->clientService = $this->createMock(IClientService::class);
        $this->clientService->method('newClient')->willReturn($this->client);

        $this->hostValidator = $this->createMock(IRemoteHostValidator::class);
        $this->hostValidator->method('isValid')->willReturn(true);

        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getClientTimeout')->willReturn(30);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');
        $this->fetcherConfig->method('getMaxRedirects')->willReturn(10);

        $this->scraper = new Scraper(
            $this->logger,
            $this->fetcherConfig,
            $this->clientService,
            $this->hostValidator
        );
    }

    public function testScrapeReturnsTrueAndSetsContent(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('<html><body>Scrape full text content</body></html>');
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('Scrape full text content', $content);
    }

    public function testHttpClientThrowsException(): void
    {
        $this->client->method('get')
            ->will($this->throwException(new \Exception('Network error')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Unable to receive content')
            );

        $result = $this->scraper->scrape('https://example.com');

        $this->assertFalse($result);
        $content = $this->scraper->getContent();
        $this->assertNull($content);
    }

    public function testConvertEncodingFromIsoToUtf8(): void
    {
        $expectedUtf8 = "Scrape full text content: äöüß ÄÖÜ ñ µ";
        $isoString = mb_convert_encoding($expectedUtf8, 'ISO-8859-1', 'UTF-8');

        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('<html><body>' . $isoString . '</body></html>');
        $response->method('getHeader')->willReturn('text/html; charset="ISO-8859-1"');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertEquals('<div>' . $expectedUtf8 . '</div>', $content);
    }

    public function testUnsupportedCharsetIsIgnored(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('<html><body>Scrape full text content</body></html>');
        $response->method('getHeader')->willReturn('text/html; charset="invalid"');
        $response->method('getHeaders')->willReturn([]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringContains('Ignoring unsupported charset')
            );

        $this->client->method('get')->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('Scrape full text content', $content);
    }

    public function testReadabilityParseThrowsException(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('Scrape invalid html content');
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')->willReturn($response);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Unable to parse content')
            );

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertNull($content);
    }

    public function testRedirectHistoryUsedAsEffectiveUrl(): void
    {
        // X-Guzzle-Redirect-History contains all redirect destinations in order;
        // the scraper should use the LAST one as the effective URL.
        // Readability rewrites relative URLs against the originalURL (= effective URL),
        // so asserting that a relative link becomes absolute against the last redirect
        // URL proves the correct URL was used.
        $finalRedirectUrl = 'https://redirected.example.com/article';

        $html = '<html><head><title>Test</title></head><body><article>'
            . '<h1>Test Article About Redirects</h1>'
            . '<p>This is a substantial article with enough content for Readability to process. '
            . 'It contains a <a href="/related">relative link</a> that should be rewritten '
            . 'against the effective URL after redirects are followed.</p>'
            . '<p>Additional paragraph to meet Readability content thresholds. '
            . 'Readability requires a reasonable amount of text within the candidate node '
            . 'before it will select it as the main article content.</p>'
            . '<p>A third paragraph further ensures the content density is high enough for '
            . 'the scoring algorithm to detect this block as the primary article body.</p>'
            . '</article></body></html>';

        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($html);
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([
            // Two hops; scraper must pick the last one
            'X-Guzzle-Redirect-History' => ['https://intermediate.example.com', $finalRedirectUrl],
        ]);

        $this->client->method('get')->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        // The relative href "/related" must be resolved against the final redirect URL,
        // NOT the original request URL.
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('https://redirected.example.com/related', $content);
        $this->assertStringNotContainsString('https://example.com/related', $content);
    }
}
