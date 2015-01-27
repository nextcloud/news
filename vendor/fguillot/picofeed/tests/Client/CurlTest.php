<?php
namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;


class CurlTest extends PHPUnit_Framework_TestCase
{
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


    public function testRedirect()
    {
        $client = new Curl;
        $client->setUrl('http://www.miniflux.net/index.html');
        $result = $client->doRequest();

        $this->assertTrue(is_array($result));
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('<!DOCTYPE', substr($result['body'], 0, 9));
        $this->assertEquals('text/html; charset=utf-8', $result['headers']['Content-Type']);
        $this->assertEquals('http://miniflux.net/', $client->getUrl());
    }

    /**
     * @expectedException PicoFeed\Client\InvalidCertificateException
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