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

    public function testFind()
    {
        $reader = new Reader;
        $resource = $reader->download('http://miniflux.net/');
        $feeds = $reader->find($resource->getUrl(), $resource->getContent());
        $this->assertTrue(is_array($feeds));
        $this->assertNotEmpty($feeds);
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]);

        $reader = new Reader;
        $resource = $reader->download('http://www.bbc.com/news/');
        $feeds = $reader->find($resource->getUrl(), $resource->getContent());
        $this->assertTrue(is_array($feeds));
        $this->assertNotEmpty($feeds);
        $this->assertEquals('http://feeds.bbci.co.uk/news/rss.xml', $feeds[0]);

        $reader = new Reader;
        $resource = $reader->download('http://www.cnn.com/services/rss/');
        $feeds = $reader->find($resource->getUrl(), $resource->getContent());
        $this->assertTrue(is_array($feeds));
        $this->assertNotEmpty($feeds);
        $this->assertTrue(count($feeds) > 1);
        $this->assertEquals('http://rss.cnn.com/rss/cnn_topstories.rss', $feeds[0]);
        $this->assertEquals('http://rss.cnn.com/rss/cnn_world.rss', $feeds[1]);
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

        $reader = new Reader;
        $client = $reader->download('http://www.groovehq.com/blog/feed');

        $parser = $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding());
        $this->assertInstanceOf('PicoFeed\Parser\Atom', $parser);

        $feed = $parser->execute();

        $this->assertEquals('http://www.groovehq.com/blog/feed', $client->getUrl());
        $this->assertEquals('http://www.groovehq.com/blog/feed', $feed->getFeedUrl());
        $this->assertNotEquals('http://www.groovehq.com/blog/feed', $feed->items[0]->getUrl());
        $this->assertTrue(strpos($feed->items[0]->getUrl(), 'http://') === 0);
        $this->assertTrue(strpos($feed->items[0]->getUrl(), 'feed') === false);
    }
}