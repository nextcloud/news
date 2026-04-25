<?php


namespace OCA\News\Tests\Unit\Fetcher;

use DateTime;
use OCA\News\Vendor\FeedIo\Adapter\Http\Response;
use OCA\News\Vendor\FeedIo\Adapter\HttpRequestException;
use OCA\News\Vendor\FeedIo\Adapter\NotFoundException;
use OCA\News\Vendor\FeedIo\Adapter\ServerErrorException;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use OCA\News\Fetcher\Client\FeedIoClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FeedIoClientTest extends TestCase
{
    /** @var FeedIoClient */
    protected FeedIoClient $class;

    /** @var IClient|MockObject */
    protected IClient|MockObject $iClient;

    protected function setUp(): void
    {
        $this->iClient = $this->getMockBuilder(IClient::class)->getMock();

        $service = $this->getMockBuilder(IClientService::class)->getMock();
        $service->method('newClient')->willReturn($this->iClient);

        $this->class = new FeedIoClient($service);
    }

    private function makeIResponse(int $status, string $body = ''): IResponse
    {
        $iResponse = $this->getMockBuilder(IResponse::class)->getMock();
        $iResponse->method('getStatusCode')->willReturn($status);
        $iResponse->method('getHeaders')->willReturn([]);
        $iResponse->method('getBody')->willReturn($body);
        return $iResponse;
    }

    public function testGetResponseSuccess(): void
    {
        $this->iClient->expects($this->once())
            ->method('get')
            ->with(
                'url',
                $this->callback(fn($opts) => ($opts['http_errors'] ?? null) === false
                    && isset($opts['headers']['If-Modified-Since']))
            )
            ->willReturn($this->makeIResponse(200));

        $result = $this->class->getResponse('url', new DateTime('@0'));
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testGetResponse404(): void
    {
        $this->expectException(NotFoundException::class);

        $this->iClient->expects($this->once())
            ->method('get')
            ->willReturn($this->makeIResponse(404));

        $this->class->getResponse('url', new DateTime('@0'));
    }

    /**
     * @dataProvider otherClientErrorProvider
     */
    public function testGetResponseOtherClientErrorThrowsHttpRequestException(int $status): void
    {
        $this->expectException(HttpRequestException::class);

        $this->iClient->expects($this->once())
            ->method('get')
            ->willReturn($this->makeIResponse($status));

        $this->class->getResponse('url', new DateTime('@0'));
    }

    public static function otherClientErrorProvider(): array
    {
        return [[400], [401], [403], [429]];
    }

    public function testGetResponseThrows(): void
    {
        $this->expectException(ServerErrorException::class);

        $this->iClient->expects($this->once())
            ->method('get')
            ->willReturn($this->makeIResponse(500));

        $this->class->getResponse('url', new DateTime('@0'));
    }

    public function testGetResponseTransportErrorThrowsHttpRequestException(): void
    {
        $this->expectException(HttpRequestException::class);

        $transportException = new class('Network unreachable') extends \RuntimeException
            implements \Psr\Http\Client\ClientExceptionInterface {};

        $this->iClient->expects($this->once())
            ->method('get')
            ->willThrowException($transportException);

        $this->class->getResponse('url', new DateTime('@0'));
    }
}
