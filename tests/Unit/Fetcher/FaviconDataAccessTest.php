<?php

namespace OCA\News\Tests\Unit\Fetcher;

use OCA\News\Config\FetcherConfig;
use OCA\News\Fetcher\FaviconDataAccess;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FaviconDataAccessTest extends TestCase
{
    /** @var FetcherConfig|MockObject */
    private $fetcherConfig;

    /** @var IClientService|MockObject */
    private $clientService;

    /** @var IClient|MockObject */
    private $client;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var FaviconDataAccess */
    private $class;

    protected function setUp(): void
    {
        $this->fetcherConfig = $this->createMock(FetcherConfig::class);
        $this->clientService = $this->createMock(IClientService::class);
        $this->client = $this->createMock(IClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->clientService->method('newClient')
            ->willReturn($this->client);

        $this->class = new FaviconDataAccess($this->fetcherConfig, $this->clientService, $this->logger);
    }

    public function testRetrieveUrlReturnsBodyOnSuccess(): void
    {
        $url = 'https://example.com/favicon.ico';
        $userAgent = 'NextCloud-News/25.0.0';

        $this->fetcherConfig->expects($this->once())
            ->method('getUserAgent')
            ->willReturn($userAgent);

        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn('favicon-body');

        $this->client->expects($this->once())
            ->method('get')
            ->with($url, [
                'timeout' => 10,
                'allow_redirects' => false,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => $userAgent,
                ],
            ])
            ->willReturn($response);

        $this->assertSame('favicon-body', $this->class->retrieveUrl($url));
    }

    public function testRetrieveUrlReturnsFalseOnClientError(): void
    {
        $url = 'https://example.com/favicon.ico';

        $this->fetcherConfig->expects($this->once())
            ->method('getUserAgent')
            ->willReturn('NextCloud-News/25.0.0');

        $this->client->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('request failed'));

        $this->logger->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->class->retrieveUrl($url));
    }

    public function testRetrieveHeaderReturnsLowercaseHeadersOnSuccess(): void
    {
        $url = 'https://example.com/favicon.ico';
        $userAgent = 'NextCloud-News/25.0.0';

        $this->fetcherConfig->expects($this->once())
            ->method('getUserAgent')
            ->willReturn($userAgent);

        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('getHeaders')
            ->willReturn([
                'Content-Type' => ['image/x-icon'],
                'X-Test' => ['1'],
            ]);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->client->expects($this->once())
            ->method('head')
            ->with($url, [
                'timeout' => 10,
                'allow_redirects' => false,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => $userAgent,
                ],
            ])
            ->willReturn($response);

        $this->assertSame([
            0 => 'HTTP/1.1 200',
            'content-type' => ['image/x-icon'],
            'x-test' => ['1'],
        ], $this->class->retrieveHeader($url));
    }

    public function testRetrieveHeaderReturnsEmptyArrayOnClientError(): void
    {
        $url = 'https://example.com/favicon.ico';
        $userAgent = 'NextCloud-News/25.0.0';

        $this->fetcherConfig->expects($this->once())
            ->method('getUserAgent')
            ->willReturn($userAgent);

        $this->client->expects($this->once())
            ->method('head')
            ->with($url, [
                'timeout' => 10,
                'allow_redirects' => false,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => $userAgent,
                ],
            ])
            ->willThrowException(new \Exception('request failed'));

        $this->logger->expects($this->once())
            ->method('warning');

        $this->assertSame([], $this->class->retrieveHeader($url));
    }
}
