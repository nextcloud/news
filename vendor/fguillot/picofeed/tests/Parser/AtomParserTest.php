<?php
namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;


class AtomParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PicoFeed\Parser\MalformedXmlException
     */
    public function testBadInput()
    {
        $parser = new Atom('boo');
        $parser->execute();
    }

    public function testFeedTitle()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('The Official Google Blog', $feed->getTitle());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Example Feed', $feed->getTitle());
    }

    public function testFeedDescription()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Insights from Googlers into our products, technology, and the Google culture.', $feed->getDescription());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getDescription());
    }

    public function testFeedLogo()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getLogo());

        $parser = new Atom(file_get_contents('tests/fixtures/bbc_urdu.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://www.bbc.co.uk/urdu/images/gel/rss_logo.gif', $feed->getLogo());
    }

    public function testFeedUrl()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getFeedUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'), '', 'http://example.org/');
        $feed = $parser->execute();
        $this->assertEquals('http://example.org/', $feed->getFeedUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/lagrange.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://www.la-grange.net/feed.atom', $feed->getFeedUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/groovehq.xml'), '', 'http://groovehq.com/');
        $feed = $parser->execute();
        $this->assertEquals('http://groovehq.com/articles.xml', $feed->getFeedUrl());
    }

    public function testSiteUrl()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://googleblog.blogspot.com/', $feed->getSiteUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://example.org/', $feed->getSiteUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/lagrange.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://www.la-grange.net/', $feed->getSiteUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/groovehq.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getSiteUrl());
    }

    public function testFeedId()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('tag:blogger.com,1999:blog-10861780', $feed->getId());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $feed->getId());
    }

    public function testFeedDate()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1360148333, $feed->getDate());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1071340202, $feed->getDate());
    }

    public function testFeedLanguage()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getLanguage());
        $this->assertEquals('', $feed->items[0]->getLanguage());

        $parser = new Atom(file_get_contents('tests/fixtures/bbc_urdu.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('ur', $feed->getLanguage());
        $this->assertEquals('ur', $feed->items[0]->getLanguage());

        $parser = new Atom(file_get_contents('tests/fixtures/lagrange.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('fr', $feed->getLanguage());
        $this->assertEquals('fr', $feed->items[0]->getLanguage());
    }

    public function testItemId()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('3841e5cf232f5111fc5841e9eba5f4b26d95e7d7124902e0f7272729d65601a6', $feed->items[0]->getId());
    }

    public function testItemUrl()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://feedproxy.google.com/~r/blogspot/MKuf/~3/S_hccisqTW8/a-chrome-experiment-made-with-some.html', $feed->items[0]->getUrl());
    }

    public function testItemTitle()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('Safer Internet Day: How we help you stay secure online', $feed->items[1]->getTitle());
    }

    public function testItemDate()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1360011661, $feed->items[1]->getDate());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1071340202, $feed->items[0]->getDate());

        $parser = new Atom(file_get_contents('tests/fixtures/youtube.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1336825342, $feed->items[1]->getDate()); // Should return the published date
    }

    public function testItemLanguage()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('', $feed->items[1]->getLanguage());
    }

    public function testItemAuthor()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('Emily Wood', $feed->items[1]->getAuthor());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('John Doe', $feed->items[0]->getAuthor());
    }

    public function testItemContent()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<p>Technology can') === 0);

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[0]->getContent(), '<p>Some text.') === 0);
    }
}