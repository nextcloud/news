<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @copyright 2024 Nextcloud GmbH and Nextcloud contributors
 */

namespace OCA\News\Tests\Unit\Fetcher;

use OCA\News\Config\FetcherConfig;
use OCA\News\Fetcher\FaviconDataAccess;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FaviconDataAccessTest extends TestCase
{
    private FetcherConfig $fetcherConfig;
    private IClientService $clientService;
    private IClient $client;
    private LoggerInterface $logger;
    private FaviconDataAccess $dataAccess;

    protected function setUp(): void
    {
        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->fetcherConfig->method('getUserAgent')->willReturn('TestAgent/1.0');

        $this->client = $this->createMock(IClient::class);

        $this->clientService = $this->createMock(IClientService::class);
        $this->clientService->method('newClient')->willReturn($this->client);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->dataAccess = new FaviconDataAccess(
            $this->fetcherConfig,
            $this->clientService,
            $this->logger
        );
    }

    public function testRetrieveUrlReturnsBodyOnSuccess(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')->willReturn('<html>Favicon page</html>');

        $this->client->expects($this->once())
            ->method('get')
            ->with(
                'https://example.com',
                $this->arrayHasKey('headers')
            )
            ->willReturn($response);

        $result = $this->dataAccess->retrieveUrl('https://example.com');

        $this->assertSame('<html>Favicon page</html>', $result);
    }

    public function testRetrieveUrlReturnsBodyForNonSuccessStatusCode(): void
    {
        // The Nextcloud HTTP client returns a response object for 4xx/5xx
        // results rather than throwing. The Favicon library depends on receiving
        // the body for any status code so it can follow redirects and detect image MIME types.
        $response = $this->createMock(IResponse::class);
        $response->method('getBody')->willReturn('Not Found');

        $this->client->method('get')->willReturn($response);
        $this->logger->expects($this->never())->method('warning');

        $result = $this->dataAccess->retrieveUrl('https://example.com');

        $this->assertSame('Not Found', $result);
    }

    public function testRetrieveUrlReturnsFalseOnClientError(): void
    {
        $this->client->method('get')
            ->will($this->throwException(new \Exception('Connection refused')));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Could not fetch favicon URL'));

        $result = $this->dataAccess->retrieveUrl('https://example.com');

        $this->assertFalse($result);
    }

    public function testRetrieveHeaderReturnsLowercaseHeadersOnSuccess(): void
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([
            'Content-Type' => 'text/html',
            'X-Custom'     => 'value',
        ]);

        $this->client->expects($this->once())
            ->method('head')
            ->with(
                'https://example.com',
                $this->arrayHasKey('headers')
            )
            ->willReturn($response);

        $result = $this->dataAccess->retrieveHeader('https://example.com');

        $this->assertSame('HTTP/1.1 200', $result[0]);
        $this->assertArrayHasKey('content-type', $result);
        $this->assertSame('text/html', $result['content-type']);
        $this->assertArrayHasKey('x-custom', $result);
    }

    public function testRetrieveHeaderReturnsEmptyArrayOnClientError(): void
    {
        $this->client->method('head')
            ->will($this->throwException(new \Exception('Connection refused')));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Could not fetch favicon headers'));

        $result = $this->dataAccess->retrieveHeader('https://example.com');

        $this->assertSame([], $result);
    }
}
