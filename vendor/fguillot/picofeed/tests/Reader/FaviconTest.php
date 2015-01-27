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
                <link rel="icon" href="http://example.com/myicon.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/myicon.ico'), $favicon->extract($html));

        // multiple values in rel attribute
        $html = '<!DOCTYPE html><html><head>
                <link rel="shortcut icon" href="http://example.com/myicon.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/myicon.ico'), $favicon->extract($html));

        // icon part of another string
        $html = '<!DOCTYPE html><html><head>
                <link rel="fluid-icon" href="http://example.com/myicon.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/myicon.ico'), $favicon->extract($html));

        // with other attributes present
        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/vnd.microsoft.icon" href="http://example.com/image.ico" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.ico'), $favicon->extract($html));

        // ignore icon in other attribute
        $html = '<!DOCTYPE html><html><head>
                <link type="icon" href="http://example.com/image.ico" />
                </head><body><p>boo</p></body></html>';

        // ignores apple icon
        $html = '<!DOCTYPE html><html><head>
                <link rel="apple-touch-icon" href="assets/img/touch-icon-iphone.png">
                <link rel="icon" type="image/png" href="http://example.com/image.png" />
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.png'), $favicon->extract($html));

        // allows multiple icons
        $html = '<!DOCTYPE html><html><head>
                <link rel="icon" type="image/png" href="http://example.com/image.png" />
                <link rel="icon" type="image/x-icon" href="http://example.com/image.ico"/>
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array('http://example.com/image.png', 'http://example.com/image.ico'), $favicon->extract($html));

        // empty array with broken html
        $html = '!DOCTYPE html html head
                link rel="icon" type="image/png" href="http://example.com/image.png" /
                link rel="icon" type="image/x-icon" href="http://example.com/image.ico"/
                /head body /p boo /p body /html';

        $this->assertEquals(array(), $favicon->extract($html));

        // empty array on no input
        $this->assertEquals(array(), $favicon->extract(''));

        // empty array on no icon found
        $html = '<!DOCTYPE html><html><head>
                </head><body><p>boo</p></body></html>';

        $this->assertEquals(array(), $favicon->extract($html));
    }

    public function testExists()
    {
        $favicon = new Favicon;

        $this->assertTrue($favicon->exists('https://en.wikipedia.org/favicon.ico'));
        $this->assertFalse($favicon->exists('http://minicoders.com/favicon.ico'));
        $this->assertFalse($favicon->exists('http://blabla'));
        $this->assertFalse($favicon->exists(''));
    }

    public function testFind_inMeta()
    {
        $favicon = new Favicon;

        // favicon in meta
        $this->assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            $favicon->find('http://miniflux.net')
        );

        $this->assertNotEmpty($favicon->getContent());
    }

//    public function testFind_inRootDir()
//    {
//        // favicon not in meta, only in website root (need example page)
//        $favicon = new Favicon;
//
//        $this->assertEquals(
//            'http://minicoders.com/favicon.ico',
//            $favicon->find('http://minicoders.com')
//        );
//    }

    public function testFind_noIcons()
    {
        $favicon = new Favicon;

        $this->assertEquals(
            '',
            $favicon->find('http://minicoders.com')
        );

        $this->assertEmpty($favicon->getContent());
    }

    public function testFind_directLinkFirst()
    {
        $favicon = new Favicon;

        $this->assertEquals(
            'http://miniflux.net/assets/img/touch-icon-ipad.png',
            $favicon->find('http://miniflux.net', '/assets/img/touch-icon-ipad.png')
        );

        $this->assertNotEmpty($favicon->getContent());
    }

    public function testFind_fallsBackToExtract()
    {
        $favicon = new Favicon;
        $this->assertEquals(
            'http://miniflux.net/assets/img/favicon.png',
            $favicon->find('http://miniflux.net','/nofavicon.ico')
        );

        $this->assertNotEmpty($favicon->getContent());
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

    public function testDataUri_withBadContentType()
    {
        $favicon = new Favicon;
        $this->assertNotEmpty($favicon->find('http://www.lemonde.fr/'));
        $expected = 'data:image/x-icon;base64,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';
        $this->assertEquals($expected, $favicon->getDataUri());
    }
}
