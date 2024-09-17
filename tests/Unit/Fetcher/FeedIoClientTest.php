<?php


namespace OCA\News\Tests\Unit\Fetcher;

use DateTime;
use FeedIo\Adapter\Guzzle\Response;
use FeedIo\Adapter\NotFoundException;
use FeedIo\Adapter\ServerErrorException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use OCA\News\Fetcher\Client\FeedIoClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FeedIoClientTest extends TestCase
{

    /**
     * @var FeedIoClient
     */
    protected $class;
    /**
     * @var ClientInterface|MockObject
     */
    protected $guzzleClient;

    protected function setUp(): void
    {
        $this->guzzleClient = $this->getMockBuilder(ClientInterface::class)
                                   ->getMock();

        $this->class = new FeedIoClient($this->guzzleClient);
    }

    public function testGetResponseSuccess(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->getMock();

        $this->guzzleClient->expects($this->once())
                           ->method('request')
                           ->with('get', 'url', ['headers' => ['If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT']])
                           ->will($this->returnValue($response));

        $result = $this->class->getResponse('url', new DateTime('@0'));
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testGetResponse404(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('error');

        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->exactly(2))
                 ->method('getStatusCode')
                 ->willReturn(404);

        $this->guzzleClient->expects($this->once())
            ->method('request')
            ->with('get', 'url', ['headers' => ['If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT']])
            ->will($this->throwException(new BadResponseException('error', $request, $response)));

        $this->class->getResponse('url', new DateTime('@0'));
    }

    public function testGetResponseThrows(): void
    {
        $this->expectException(ServerErrorException::class);
        $this->expectExceptionMessage('error');

        $request = $this->getMockBuilder(RequestInterface::class)
                         ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
                         ->getMock();

        $this->guzzleClient->expects($this->once())
                           ->method('request')
                           ->with('get', 'url', ['headers' => ['If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT']])
                           ->will($this->throwException(new BadResponseException('error', $request, $response)));

        $this->class->getResponse('url', new DateTime('@0'));
    }
}
