<?php
namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;


class CurlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group online
     */
    public function testDownload()
    {
        $client = new Curl;
        $client->setUrl('http://miniflux.net/index.html');
        $result = $client->doRequest();

        $this->assertTrue(is_array($result));
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('<!DOC', substr($result['body'], 0, 5));
        $this->assertEquals('text/html; charset=utf-8', $result['headers']['Content-Type']);
    }

    /**
     * @runInSeparateProcess
     * @group online
     */
    public function testPassthrough()
    {
        $client = new Curl;
        $client->setUrl('https://miniflux.net/favicon.ico');
        $client->enablePassthroughMode();
        $client->doRequest();

        $this->expectOutputString(file_get_contents('tests/fixtures/miniflux_favicon.ico'));
    }

    /**
     * @group online
     */
    public function testRedirect()
    {
        $client = new Curl;
        $client->setUrl('http://rss.feedsportal.com/c/629/f/502199/s/42e50391/sc/44/l/0L0S0A1net0N0Ceditorial0C6437220Candroid0Egoogle0Enow0Es0Eouvre0Eaux0Eapplications0Etierces0C0T0Dxtor0FRSS0E16/story01.htm');
        $result = $client->doRequest();

        $this->assertTrue(is_array($result));
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('<!DOCTYPE', substr($result['body'], 0, 9));
        $this->assertEquals('text/html', $result['headers']['Content-Type']);
        $this->assertEquals('http://www.01net.com/editorial/643722/android-google-now-s-ouvre-aux-applications-tierces/', str_replace('#?xtor=RSS-16', '', $client->getUrl()));
    }

    /**
     * @expectedException PicoFeed\Client\InvalidCertificateException
     * @group online
     */
    public function testSSL()
    {
        $client = new Curl;
        $client->setUrl('https://www.mjvmobile.com.br');
        $client->doRequest();
    }

    /**
     * @expectedException PicoFeed\Client\InvalidUrlException
     */
    public function testBadUrl()
    {
        $client = new Curl;
        $client->setUrl('http://12345gfgfgf');
        $client->doRequest();
    }
}