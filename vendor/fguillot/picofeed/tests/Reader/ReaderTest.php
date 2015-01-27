<?php
namespace PicoFeed\Reader;

use PHPUnit_Framework_TestCase;


class ReaderTest extends PHPUnit_Framework_TestCase
{
    public function testPrependScheme()
    {
        $reader = new Reader;
        $this->assertEquals('http://http.com', $reader->prependScheme('http.com'));
        $this->assertEquals('http://boo.com', $reader->prependScheme('boo.com'));
        $this->assertEquals('http://google.com', $reader->prependScheme('http://google.com'));
        $this->assertEquals('https://google.com', $reader->prependScheme('https://google.com'));
    }

    public function testDownload_withHTTP()
    {
        $reader = new Reader;
        $feed = $reader->download('http://wordpress.org/news/feed/')->getContent();
        $this->assertNotEmpty($feed);
    }

    public function testDownload_withHTTPS()
    {
        $reader = new Reader;
        $feed = $reader->download('https://wordpress.org/news/feed/')->getContent();
        $this->assertNotEmpty($feed);
    }

    public function testDownload_withCache()
    {
        $reader = new Reader;
        $resource = $reader->download('http://linuxfr.org/robots.txt');
        $this->assertTrue($resource->isModified());

        $lastModified = $resource->getLastModified();
        $etag = $resource->getEtag();

        $reader = new Reader;
        $resource = $reader->download('http://linuxfr.org/robots.txt', $lastModified, $etag);
        $this->assertFalse($resource->isModified());
    }

    public function testDetectFormat()
    {
        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/podbean.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/jeux-linux.fr.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/sametmax.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss92', $reader->detectFormat(file_get_contents('tests/fixtures/rss_0.92.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss91', $reader->detectFormat(file_get_contents('tests/fixtures/rss_0.91.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss10', $reader->detectFormat(file_get_contents('tests/fixtures/planete-jquery.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/rss2sample.xml')));

        $reader = new Reader;
        $this->assertEquals('Atom', $reader->detectFormat(file_get_contents('tests/fixtures/atomsample.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/cercle.psy.xml')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/ezrss.it')));

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat(file_get_contents('tests/fixtures/grotte_barbu.xml')));

        $content = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" media="screen" href="/~d/styles/rss2titles.xsl"?><?xml-stylesheet type="text/css" media="screen" href="http://feeds.feedburner.com/~d/styles/itemtitles.css"?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:feedburner="http://rssnamespace.org/feedburner/ext/1.0" version="2.0">';

        $reader = new Reader;
        $this->assertEquals('Rss20', $reader->detectFormat($content));
    }

    public function testFind_rssFeed()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link type="application/rss+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array('http://miniflux.net/feed'), $feeds);
    }

    public function testFind_atomFeed()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link type="application/atom+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array('http://miniflux.net/feed'), $feeds);
    }

    public function testFind_feedNotInHead()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head></head>
                <body>
                <link type="application/atom+xml" href="http://miniflux.net/feed">
                <p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array('http://miniflux.net/feed'), $feeds);
    }

    public function testFind_noFeedPresent()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array(), $feeds);
    }

    public function testFind_ignoreUnknownType()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link type="application/flux+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array(), $feeds);
    }

    public function testFind_ignoreTypeInOtherAttribute()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link rel="application/rss+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array(), $feeds);
    }

    public function testFind_withOtherAttributesPresent()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link rel="alternate" type="application/rss+xml" title="RSS" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://miniflux.net/', $html);
        $this->assertEquals(array('http://miniflux.net/feed'), $feeds);
    }

    public function testFind_multipleFeeds()
    {
        $reader = new Reader;

        $html = '<!DOCTYPE html><html><head>
                <link rel="alternate" type="application/rss+xml" title="CNN International: Top Stories" href="http://rss.cnn.com/rss/edition.rss"/>
                <link rel="alternate" type="application/rss+xml" title="Connect The World" href="http://rss.cnn.com/rss/edition_connecttheworld.rss"/>
                <link rel="alternate" type="application/rss+xml" title="World Sport" href="http://rss.cnn.com/rss/edition_worldsportblog.rss"/>
                </head><body><p>boo</p></body></html>';

        $feeds = $reader->find('http://www.cnn.com/services/rss/', $html);
        $this->assertEquals(
                array(
                    'http://rss.cnn.com/rss/edition.rss',
                    'http://rss.cnn.com/rss/edition_connecttheworld.rss',
                    'http://rss.cnn.com/rss/edition_worldsportblog.rss'
                ),
                $feeds
        );
    }

    public function testFind_withInvalidHTML()
    {
        $reader = new Reader;

        $html = '!DOCTYPE html html head
                link type="application/rss+xml" href="http://miniflux.net/feed"
                /head body /p boo /p body /html';

        $feeds = $reader->find('http://miniflux.net/', '');
        $this->assertEquals(array(), $feeds);
    }

    public function testFind_withHtmlParamEmptyString()
    {
        $reader = new Reader;

        $feeds = $reader->find('http://miniflux.net/', '');
        $this->assertEquals(array(), $feeds);
    }

    public function testDiscover()
    {
        $reader = new Reader;
        $client = $reader->discover('http://www.universfreebox.com/');
        $this->assertEquals('http://www.universfreebox.com/backend.php', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding()));

        $reader = new Reader;
        $client = $reader->discover('http://planete-jquery.fr');
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding()));

        $reader = new Reader;
        $client = $reader->discover('http://cabinporn.com/');
        $this->assertEquals('http://cabinporn.com/rss', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding()));

        $reader = new Reader;
        $client = $reader->discover('http://linuxfr.org/');
        $this->assertEquals('http://linuxfr.org/news.atom', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\Atom', $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding()));
    }

    public function testFeedsReportedAsNotWorking()
    {
        $reader = new Reader;
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser('http://blah', file_get_contents('tests/fixtures/cercle.psy.xml'), 'utf-8'));

        $reader = new Reader;
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser('http://blah', file_get_contents('tests/fixtures/ezrss.it'), 'utf-8'));

        $reader = new Reader;
        $this->assertInstanceOf('PicoFeed\Parser\Rss20', $reader->getParser('http://blah', file_get_contents('tests/fixtures/grotte_barbu.xml'), 'utf-8'));
    }
}
