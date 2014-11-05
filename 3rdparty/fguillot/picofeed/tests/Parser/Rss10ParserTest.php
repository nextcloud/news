<?php
namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;


class Rss10ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PicoFeed\Parser\MalformedXmlException
     */
    public function testBadInput()
    {
        $parser = new Rss10('boo');
        $parser->execute();
    }

    public function testFeedTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertEquals("Planète jQuery : l'actualité jQuery, plugins jQuery et tutoriels jQuery en français", $feed->getTitle());
    }

    public function testFeedUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://planete-jquery.fr', $feed->getUrl());
    }

    public function testFeedId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://planete-jquery.fr', $feed->getId());
    }

    public function testFeedDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1363752990, $feed->getDate());
    }

    public function testFeedLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertEquals('fr', $feed->getLanguage());
        $this->assertEquals('fr', $feed->items[0]->getLanguage());
    }

    public function testItemId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);

        $item = $feed->items[0];
        $this->assertEquals($parser->generateId($item->getTitle(), $item->getUrl(), $item->getContent()), $item->getId());
    }

    public function testItemUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://www.mathieurobin.com/2013/03/chroniques-jquery-episode-108/', $feed->items[0]->getUrl());
    }

    public function testItemTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('LaFermeDuWeb : PowerTip - Des tooltips aux fonctionnalités avancées', $feed->items[1]->getTitle());
    }

    public function testItemDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1362647700, $feed->items[1]->getDate());
    }

    public function testItemLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('fr', $feed->items[1]->getLanguage());
    }

    public function testItemAuthor()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('LaFermeDuWeb', $feed->items[1]->getAuthor());
    }

    public function testItemContent()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<a href="http://www.lafermeduweb.net') === 0);
    }
}