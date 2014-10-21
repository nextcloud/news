<?php

require_once 'lib/PicoFeed/PicoFeed.php';

use PicoFeed\Reader;

class ReaderTest extends PHPUnit_Framework_TestCase
{
    public function testDownload()
    {
        $reader = new Reader;
        $feed = $reader->download('http://wordpress.org/news/feed/')->getContent();
        $this->assertNotEmpty($feed);
    }


    public function testDownloadWithCache()
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
        $reader->setContent(file_get_contents('tests/fixtures/jeux-linux.fr.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/sametmax.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/rss_0.92.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss92', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/rss_0.91.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss91', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss10', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/rss2sample.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/atomsample.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Atom', $reader->getParser());

        $reader = new Reader;
        $this->assertFalse($reader->getParser());

        $reader = new Reader;
        $reader->setContent('<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" media="screen" href="/~d/styles/rss2titles.xsl"?><?xml-stylesheet type="text/css" media="screen" href="http://feeds.feedburner.com/~d/styles/itemtitles.css"?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:feedburner="http://rssnamespace.org/feedburner/ext/1.0" version="2.0">');
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());
    }


    public function testRemoteDetection()
    {
        $reader = new Reader;
        $reader->download('http://www.universfreebox.com/');
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->download('http://planete-jquery.fr');
        $this->assertTrue($reader->discover());

        $reader = new Reader;
        $reader->download('http://cabinporn.com/');
        $this->assertTrue($reader->discover());
        $this->assertEquals('http://cabinporn.com/rss', $reader->getUrl());

        $reader = new Reader;
        $reader->download('http://linuxfr.org/');
        $this->assertTrue($reader->discover());
        $this->assertEquals('http://linuxfr.org/news.atom', $reader->getUrl());
    }


    public function testFeedsReportedAsNotWorking()
    {
        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/cercle.psy.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/ezrss.it'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());

        $reader = new Reader;
        $reader->setContent(file_get_contents('tests/fixtures/grotte_barbu.xml'));
        $this->assertInstanceOf('PicoFeed\Parsers\Rss20', $reader->getParser());
    }
}