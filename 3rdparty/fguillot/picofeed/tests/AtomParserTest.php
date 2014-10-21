<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Parsers/Atom.php';

use PicoFeed\Parsers\Atom;

class AtomParserTest extends PHPUnit_Framework_TestCase
{
    public function testBadInput()
    {
        $parser = new Atom('boo');
        $this->assertFalse($parser->execute());
    }

    public function testFeedTitle()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('The Official Google Blog', $feed->getTitle());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('Example Feed', $feed->getTitle());
    }

    public function testFeedDescription()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('Insights from Googlers into our products, technology, and the Google culture.', $feed->getDescription());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('', $feed->getDescription());
    }

    public function testFeedLogo()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('', $feed->getLogo());

        $parser = new Atom(file_get_contents('tests/fixtures/bbc_urdu.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://www.bbc.co.uk/urdu/images/gel/rss_logo.gif', $feed->getLogo());
    }

    public function testFeedUrl()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://googleblog.blogspot.com/', $feed->getUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://example.org/', $feed->getUrl());

        $parser = new Atom(file_get_contents('tests/fixtures/lagrange.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://www.la-grange.net/', $feed->getUrl());
    }

    public function testFeedId()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('tag:blogger.com,1999:blog-10861780', $feed->getId());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $feed->getId());
    }

    public function testFeedDate()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals(1360148333, $feed->getDate());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals(1071340202, $feed->getDate());
    }

    public function testFeedLanguage()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('', $feed->getLanguage());
        $this->assertEquals('', $feed->items[0]->getLanguage());

        $parser = new Atom(file_get_contents('tests/fixtures/bbc_urdu.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('ur', $feed->getLanguage());
        $this->assertEquals('ur', $feed->items[0]->getLanguage());

        $parser = new Atom(file_get_contents('tests/fixtures/lagrange.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('fr', $feed->getLanguage());
        $this->assertEquals('fr', $feed->items[0]->getLanguage());
    }

    public function testItemId()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('34ce9ad8', $feed->items[0]->getId());
    }

    public function testItemUrl()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://feedproxy.google.com/~r/blogspot/MKuf/~3/S_hccisqTW8/a-chrome-experiment-made-with-some.html', $feed->items[0]->getUrl());
    }

    public function testItemTitle()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('Safer Internet Day: How we help you stay secure online', $feed->items[1]->getTitle());
    }

    public function testItemDate()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1360011661, $feed->items[1]->getDate());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1071340202, $feed->items[0]->getDate());
    }

    public function testItemLanguage()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('', $feed->items[1]->getLanguage());
    }

    public function testItemAuthor()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('Emily Wood', $feed->items[1]->getAuthor());

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('John Doe', $feed->items[0]->getAuthor());
    }

    public function testItemContent()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<p>Technology can') === 0);

        $parser = new Atom(file_get_contents('tests/fixtures/atomsample.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[0]->getContent(), '<p>Some text.') === 0);
    }
/*
    public function testItemEnclosure()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/rue89.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://rue89.feedsportal.com/c/33822/f/608948/e/1/s/2a687021/l/0L0Srue890N0Csites0Cnews0Cfiles0Cstyles0Cmosaic0Cpublic0Czapnet0Cthumbnail0Isquare0C20A130C0A40Ccahuzac0I10Bpng/cahuzac_1.png', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('image/png', $feed->items[0]->getEnclosureType());
    }
*/
}