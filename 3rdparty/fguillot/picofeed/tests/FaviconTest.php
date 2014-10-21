<?php

require_once 'lib/PicoFeed/PicoFeed.php';

use PicoFeed\Favicon;
use PicoFeed\Url;

class FaviconTest extends PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $favicon = new Favicon;

        $html = '<!DOCTYPE html><html><head>
                <link rel="shortcut icon" href="http://example.com/myicon.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/myicon.ico'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" href="http://example.com/myicon.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/myicon.ico'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/vnd.microsoft.icon" href="http://example.com/image.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.ico'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/png" href="http://example.com/image.png" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.png'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/gif" href="http://example.com/image.gif" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.gif'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/x-icon" href="http://example.com/image.ico"/>
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.ico'), $favicon->extract($html));

        $html = '<!DOCTYPE html><html><head>
                <link rel="apple-touch-icon" href="assets/img/touch-icon-iphone.png">
                <link rel="icon" type="image/png" href="http://example.com/image.png" />
                <link rel="icon" type="image/x-icon" href="http://example.com/image.ico"/>
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.png', 'http://example.com/image.ico'), $favicon->extract($html));
    }
/*
    public function testHasFile()
    {
        $favicon = new Favicon;
        $this->assertTrue($favicon->exists('https://en.wikipedia.org/favicon.ico'));
        $this->assertFalse($favicon->exists('http://minicoders.com/favicon.ico'));
        $this->assertFalse($favicon->exists('http://blabla'));
    }
*/
    public function testConvertLink()
    {
        $favicon = new Favicon;

        $this->assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            $favicon->convertLink(new Url('http://miniflux.net'), new Url('assets/img/favicon.png'))
        );

        $this->assertEquals(
            'https://miniflux.net/assets/img/favicon.png',
            $favicon->convertLink(new Url('https://miniflux.net'), new Url('assets/img/favicon.png'))
        );

        $this->assertEquals(
            'http://google.com/assets/img/favicon.png',
            $favicon->convertLink(new Url('http://miniflux.net'), new Url('//google.com/assets/img/favicon.png'))
        );

        $this->assertEquals(
            'https://google.com/assets/img/favicon.png',
            $favicon->convertLink(new Url('https://miniflux.net'), new Url('//google.com/assets/img/favicon.png'))
        );
    }

    public function testFind()
    {
        $favicon = new Favicon;

        // Relative favicon in html
        $this->assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            $favicon->find('http://miniflux.net')
        );

        $this->assertNotEmpty($favicon->getContent());

        // Absolute html favicon
        $this->assertEquals(
            'http://php.net/favicon.ico',
            $favicon->find('http://php.net/parse_url')
        );

        $this->assertNotEmpty($favicon->getContent());

        // Protocol relative favicon
        $this->assertEquals(
            'https://bits.wikimedia.org/favicon/wikipedia.ico',
            $favicon->find('https://en.wikipedia.org/')
        );

        $this->assertNotEmpty($favicon->getContent());

        // fluid-icon + https
        $this->assertEquals(
            'https://github.com/fluidicon.png',
            $favicon->find('https://github.com')
        );

        $this->assertNotEmpty($favicon->getContent());

        // favicon in meta
        $this->assertEquals(
            'http://www.microsoft.com/favicon.ico?v2',
            $favicon->find('http://www.microsoft.com')
        );

        $this->assertNotEmpty($favicon->getContent());

        // no icon
        $this->assertEquals(
            '',
            $favicon->find('http://minicoders.com/favicon.ico')
        );

        $this->assertEmpty($favicon->getContent());
    }
}
