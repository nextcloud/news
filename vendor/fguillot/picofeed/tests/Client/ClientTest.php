<?php

namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testDownload()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();

        $this->assertTrue($client->isModified());
        $this->assertNotEmpty($client->getContent());
        $this->assertNotEmpty($client->getEtag());
        $this->assertNotEmpty($client->getLastModified());
    }

//    // disabled due to https://github.com/sebastianbergmann/phpunit/issues/1452
//    /**
//     * @runInSeparateProcess
//     */
//    public function testPassthrough()
//    {
//        $client = Client::getInstance();
//        $client->setUrl('http://miniflux.net/favicon.ico');
//        $client->enablePassthroughMode();
//        $client->execute();
//
//        $this->expectOutputString('no_to_be_defined');
//    }

    public function testCacheBothHaveToMatch()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        $etag = $client->getEtag();

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->setEtag($etag);
        $client->execute();

        $this->assertTrue($client->isModified());
    }

    public function testCacheEtag()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        $etag = $client->getEtag();
        $lastModified = $client->getLastModified();

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->setEtag($etag);
        $client->setLastModified($lastModified);
        $client->execute();

        $this->assertFalse($client->isModified());
    }

    public function testCacheLastModified()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->execute();
        $lastmod = $client->getLastModified();

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->setLastModified($lastmod);
        $client->execute();

        $this->assertFalse($client->isModified());
    }

    public function testCacheBoth()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->execute();
        $lastmod = $client->getLastModified();
        $etag = $client->getEtag();

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/humans.txt');
        $client->setLastModified($lastmod);
        $client->setEtag($etag);
        $client->execute();

        $this->assertFalse($client->isModified());
    }

    public function testCharset()
    {
        $client = Client::getInstance();
        $client->setUrl('http://php.net/');
        $client->execute();
        $this->assertEquals('utf-8', $client->getEncoding());

        $client = Client::getInstance();
        $client->setUrl('http://php.net/robots.txt');
        $client->execute();
        $this->assertEquals('', $client->getEncoding());
    }

    public function testContentType()
    {
        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/assets/img/favicon.png');
        $client->execute();
        $this->assertEquals('image/png', $client->getContentType());

        $client = Client::getInstance();
        $client->setUrl('http://miniflux.net/');
        $client->execute();
        $this->assertEquals('text/html; charset=utf-8', $client->getContentType());
    }
}
