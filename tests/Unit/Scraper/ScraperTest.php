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
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

class ScraperTest extends TestCase
{
    private $logger;
    private $fetcherConfig;
    private $httpClient;
    private $scraper;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->httpClient = $this->getMockBuilder(Client::class)->getMock();

        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getHttpClient')
            ->willReturn($this->httpClient);

        $this->scraper = new Scraper($this->logger, $this->fetcherConfig);
    }

    public function testScrapeReturnsTrueAndSetsContent(): void
    {
        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $body->method('getContents')->willReturn('<html><body>Scrape full text content</body></html>');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        $this->httpClient->method('request')
                         ->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('Scrape full text content', $content);
    }

    public function testHttpClientThrowsException(): void
    {
        $this->httpClient->method('request')
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

        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $body->method('getContents')->willReturn('<html><body>'.$isoString.'</body></html>');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaderLine')->willReturn('text/html; charset="ISO-8859-1"');

        $this->httpClient->method('request')
            ->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertEquals('<div>'.$expectedUtf8.'</div>', $content);
    }

    public function testUnsupportedCharsetIsIgnored(): void
    {
        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $body->method('getContents')->willReturn('<html><body>Scrape full text content</body></html>');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaderLine')->willReturn('text/html; charset="invalid"');

        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringContains('Ignoring unsupported charset')
            );

        $this->httpClient->method('request')
            ->willReturn($response);

        $result = $this->scraper->scrape('https://example.com');

        $this->assertTrue($result);
        $content = $this->scraper->getContent();
        $this->assertStringContainsString('Scrape full text content', $content);
    }

    public function testReadabilityParseThrowsException(): void
    {
        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $body->method('getContents')->willReturn('Scrape invalid html content');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        $this->httpClient->method('request')
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
}
