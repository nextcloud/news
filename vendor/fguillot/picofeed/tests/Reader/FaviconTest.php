<?php

namespace PicoFeed\Reader;

use PHPUnit_Framework_TestCase;
use PicoFeed\Client\Url;

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

    public function testDataUri()
    {
        $favicon = new Favicon;

        $this->assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            $favicon->find('http://miniflux.net')
        );

        $expected = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAGJwAABicBTVTYxwAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAALMSURBVHic7Zo7a1RRFIW/I8YXaBBEJRJEU8RqQBBBQRBEWxHBwlZUsLRWUFBsA4L4G4IY0TaF2PhEEQwmhuADJIkRUUOMr2RZ3Em8mcxkzrkPtjhnwS7msveadT/Ofc44SbSyllkHsFYEYB3AWhGAdQBrRQDWAawVAVgHsFYEYB3AWhGAdQBrLS/L2Dm3CdgFbK3WDPC6Wi8kjWX03QBUgG3AdmAN8LFaT4CnCnjEdbW9zrk+YL3n/AVJd2vmDwKngMNAW4O538BNoEfSfa+gzu0DzgBHl/AFGAN6gcuSPjQ1lrSggHFAnnUsNdcO3AiYnas7wNraHCnfLcC9DL6TwNlGvvP+RQAAdgIjGULO1XOgs06WQ8BEDl8BPVRXeikAgK4CQgp4B7SnchwnOW/k9RVwviwAp4HBgkIKuJ5aUd8K9P0JVMoA8LnAkAJmgSPA24J9BfTXA1DvKjAObOT/k4BuScPpjWXcCM0Co8CnErynSFbHTIZZB5xYtDXnIZCuCeAkqUsa0AlcyeiXrtvAnpTvamA/8CbQ50HR54C5egV0LHEtv5hj588t4dsBvA/wmgbaigbwneTYanyzkayELDvf2/RGBi4FelaKBnC1Wciq70Cg7y+gy8O3O9D3QHq+iJPgNc++R4G+/ZJGPPqGSU68vlqX/pAXwKCkl569XwK9b/k0SZoleRL0VaEAngX0TgZ6Pw7obf7U91cr0x/yAhgK6A0BIMB3ZUFyq5tJeQGELL2vAb1TkqYD+lcF9C5QXgAhO/WjJF/I8WYrL4CQnfoXfBep5V+KRgDWAawVAVgHsFYEYB3AWhGAdQBrRQDWAawVAVgHsFYEYB3AWi0PoN6Po3uBFZ7zA5ImvL7Iuc3ADk/faUkPPXtxzu0m+a+Qj4Ykjc7P1gJoNbX8IRABWAewVgRgHcBaEYB1AGtFANYBrBUBWAewVssD+AMBy6wzsaDiAwAAAABJRU5ErkJggg==';

        $this->assertEquals($expected, $favicon->getDataUri());
    }
}
