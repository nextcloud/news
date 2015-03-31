<?php
namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;


class StreamTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group online
     */
    public function testChunkedResponse()
    {
        $client = new Stream;
        $client->setUrl('http://www.reddit.com/r/dwarffortress/.rss');
        $result = $client->doRequest();

        $this->assertEquals('</rss>', substr($result['body'], -6));
    }

    /**
     * @group online
     */
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

    /**
     * @runInSeparateProcess
     * @group online
     */
    public function testPassthrough()
    {
        $client = new Stream;
        $client->setUrl('http://miniflux.net/favicon.ico');
        $client->enablePassthroughMode();
        $client->doRequest();

        $this->expectOutputString(file_get_contents('tests/fixtures/miniflux_favicon.ico'));
    }

    /**
     * @group online
     */
    public function testRedirect()
    {
        $client = new Stream;
        $client->setUrl('http://rss.feedsportal.com/c/629/f/502199/s/42e50391/sc/44/l/0L0S0A1net0N0Ceditorial0C6437220Candroid0Egoogle0Enow0Es0Eouvre0Eaux0Eapplications0Etierces0C0T0Dxtor0FRSS0E16/story01.htm');
        $result = $client->doRequest();

        $this->assertTrue(is_array($result));
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('<!DOCTYPE', substr($result['body'], 0, 9));
        $this->assertEquals('text/html', $result['headers']['Content-Type']);
        $this->assertEquals('http://www.01net.com/editorial/643722/android-google-now-s-ouvre-aux-applications-tierces/#?xtor=RSS-16', $client->getUrl());
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

    /**
     * @group online
     */
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