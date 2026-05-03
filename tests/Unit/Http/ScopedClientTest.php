<?php

declare(strict_types=1);

namespace OCA\News\Tests\Unit\Http;

use OCA\News\Http\ScopedClient;
use OCA\News\Vendor\GuzzleHttp\Psr7\Request;
use OCA\News\Vendor\Psr\Http\Client\NetworkExceptionInterface;
use OCA\News\Vendor\Psr\Http\Client\RequestExceptionInterface;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopedClientTest extends TestCase
{
    private IClient|MockObject $iClient;

    private ScopedClient $client;

    protected function setUp(): void
    {
        $this->iClient = $this->createMock(IClient::class);
        $this->client = new ScopedClient($this->iClient, [
            'timeout' => 12,
            'allow_redirects' => ['max' => 3, 'referer' => true, 'track_redirects' => true],
        ]);
    }

    private function makeResponse(int $statusCode, string $body = '', array $headers = []): IResponse
    {
        $response = $this->createMock(IResponse::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaders')->willReturn($headers);

        return $response;
    }

    public function testSendRequestGetPassesExpectedOptionsAndReturnsResponse(): void
    {
        $request = new Request('GET', 'https://example.com', ['Accept' => ['application/json']]);

        $this->iClient->expects($this->once())
            ->method('get')
            ->with(
                'https://example.com',
                $this->callback(function (array $options): bool {
                    return ($options['timeout'] ?? null) === 12
                        && ($options['http_errors'] ?? null) === false
                        && ($options['allow_redirects']['track_redirects'] ?? null) === true
                        && ($options['headers']['Accept'] ?? null) === 'application/json';
                })
            )
            ->willReturn($this->makeResponse(200, 'ok', ['Content-Type' => 'text/plain']));

        $response = $this->client->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', (string) $response->getBody());
        $this->assertSame(['text/plain'], $response->getHeader('Content-Type'));
    }

    public function testSendRequestHeadCallsHeadMethod(): void
    {
        $request = new Request('HEAD', 'https://example.com/icon.ico');

        $this->iClient->expects($this->once())
            ->method('head')
            ->with('https://example.com/icon.ico', $this->isType('array'))
            ->willReturn($this->makeResponse(204));

        $response = $this->client->sendRequest($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testSendRequestDoesNotThrowOnHttpClientErrorStatuses(): void
    {
        $request = new Request('GET', 'https://example.com/missing.ico');

        $this->iClient->expects($this->once())
            ->method('get')
            ->willReturn($this->makeResponse(404, 'not found'));

        $response = $this->client->sendRequest($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('not found', (string) $response->getBody());
    }

    public function testSendRequestUnsupportedMethodThrowsScopedRequestException(): void
    {
        $request = new Request('POST', 'https://example.com');

        $this->expectException(RequestExceptionInterface::class);

        $this->client->sendRequest($request);
    }

    public function testSendRequestWrapsTransportException(): void
    {
        $request = new Request('GET', 'https://example.com');
        $transportException = new \RuntimeException('network down');

        $this->iClient->expects($this->once())
            ->method('get')
            ->willThrowException($transportException);

        try {
            $this->client->sendRequest($request);
            $this->fail('Expected NetworkExceptionInterface to be thrown');
        } catch (NetworkExceptionInterface $e) {
            $this->assertSame($request, $e->getRequest());
            $this->assertSame($transportException, $e->getPrevious());
            $this->assertStringContainsString('network down', $e->getMessage());
        }
    }
}
