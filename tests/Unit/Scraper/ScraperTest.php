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
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;
use OCP\Security\IRemoteHostValidator;
use Psr\Log\LoggerInterface;

class ScraperTest extends TestCase
{
    private $logger;
    private $fetcherConfig;
    private $clientService;
    private $client;
    private $hostValidator;
    private $scraper;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->client = $this->createMock(IClient::class);
        $this->clientService = $this->createMock(IClientService::class);
        $this->clientService->method('newClient')
            ->willReturn($this->client);

        $this->hostValidator = $this->createMock(IRemoteHostValidator::class);
        $this->hostValidator->method('isValid')->willReturn(true);

        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getUserAgent')->willReturn('NextCloud-News/25.0.0');
        $this->fetcherConfig->method('getClientTimeout')->willReturn(30);

        $this->scraper = new Scraper($this->logger, $this->fetcherConfig, $this->clientService, $this->hostValidator);
    }

    public function testScrapeReturnsTrueAndSetsContent(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')->willReturn('<html><body>Scrape full text content</body></html>');
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')
            ->willReturn($response);

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
        $response->method('getBody')->willReturn('<html><body>'.$isoString.'</body></html>');
        $response->method('getHeader')->willReturn('text/html; charset="ISO-8859-1"');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')
            ->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertEquals('<div>'.$expectedUtf8.'</div>', $content);
    }

    public function testUnsupportedCharsetIsIgnored(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')->willReturn('<html><body>Scrape full text content</body></html>');
        $response->method('getHeader')->willReturn('text/html; charset="invalid"');
        $response->method('getHeaders')->willReturn([]);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringContains('Ignoring unsupported charset')
            );

        $this->client->method('get')
            ->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('Scrape full text content', $content);
    }

    public function testReadabilityParseThrowsException(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')->willReturn('Scrape invalid html content');
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([]);

        $this->client->method('get')
            ->willReturn($response);

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
        // X-Guzzle-Redirect-History contains redirect destinations newest-first;
        // the first element is the URL the final response was served from.
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')
            ->willReturn('<html><body><p>Article with <img src="/logo.png"> image</p></body></html>');
        $response->method('getHeader')->willReturn('');
        $response->method('getHeaders')->willReturn([
            'X-Guzzle-Redirect-History' => ['https://redirected.example.com'],
        ]);

        $this->client->method('get')->willReturn($response);

        $result = $this->scraper->scrape('https://short.example.com/abc');

        $this->assertTrue($result);
        // The relative /logo.png should be resolved against the redirected domain,
        // not the original short URL.
        $this->assertStringContainsString('https://redirected.example.com/logo.png', $this->scraper->getContent());
    }
}
