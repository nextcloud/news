<?php
namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;


class StreamTest extends PHPUnit_Framework_TestCase
{
    public function testChunkedResponse()
    {
        $client = new Stream;
        $client->setUrl('http://www.reddit.com/r/dwarffortress/.rss');
        $result = $client->doRequest();

        $this->assertEquals('</rss>', substr($result['body'], -6));
    }

    public function testDownload()
    {
        $client = new Stream;
        $client->setUrl('https://github.com/fguillot/picoFeed');
        $result = $client->doRequest();

        $this->assertEquals(200, $result['status']);
        $this->assertEquals('text/html; charset=utf-8', $result['headers']['Content-Type']);
        $this->assertEquals('<!DOCTYPE html>', substr(trim($result['body']), 0, 15));
        $this->assertEquals('</html>', substr(trim($result['body']), -7));
    }

    public function testRedirect()
    {
        $client = new Stream;
        $client->setUrl('http://www.miniflux.net/index.html');
        $result = $client->doRequest();

        $this->assertTrue(is_array($result));
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('<!DOCTYPE', substr($result['body'], 0, 9));
        $this->assertEquals('text/html; charset=utf-8', $result['headers']['Content-Type']);
        $this->assertEquals('http://miniflux.net/', $client->getUrl());
    }

    /**
     * @expectedException PicoFeed\Client\InvalidUrlException
     */
    public function testBadUrl()
    {
        $client = new Stream;
        $client->setUrl('http://12345gfgfgf');
        $client->setTimeout(1);
        $client->doRequest();
    }

    public function testDecodeGzip()
    {
        if (function_exists('gzdecode')) {
            $client = new Stream;
            $client->setUrl('https://github.com/fguillot/picoFeed');
            $result = $client->doRequest();

            $this->assertEquals('gzip', $result['headers']['Content-Encoding']);
            $this->assertEquals('<!DOC', substr(trim($result['body']), 0, 5));
        }
    }
}