<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Clients/Curl.php';

use PicoFeed\Clients\Curl;

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
    }


    // public function testInfiniteRedirect()
    // {
    //     $client = new Curl;
    //     $client->url = 'http://www.accupass.com/home/rss/%E8%AA%B2%E7%A8%8B%E8%AC%9B%E5%BA%A7';
    //     $result = $client->doRequest();

    //     $this->assertFalse($result);
    // }


    public function testBadUrl()
    {
        $client = new Curl;
        $client->setUrl('http://12345gfgfgf');
        $result = $client->doRequest();

        $this->assertFalse($result);
    }


    // public function testAbortOnLargeBody()
    // {
    //     $client = new Curl;
    //     $client->setUrl('http://duga.jp/ror.xml');
    //     $result = $client->doRequest();

    //     $this->assertFalse($result);
    // }
}