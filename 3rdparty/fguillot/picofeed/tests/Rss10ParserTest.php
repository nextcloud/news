<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Parsers/Rss10.php';

use PicoFeed\Parsers\Rss10;

class Rss10ParserTest extends PHPUnit_Framework_TestCase
{
    public function testBadInput()
    {
        $parser = new Rss10('boo');
        $this->assertFalse($parser->execute());
    }

    public function testFeedTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals("Planète jQuery : l'actualité jQuery, plugins jQuery et tutoriels jQuery en français", $feed->getTitle());
    }

    public function testFeedUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://planete-jquery.fr', $feed->getUrl());
    }

    public function testFeedId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('http://planete-jquery.fr', $feed->getId());
    }

    public function testFeedDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals(1363752990, $feed->getDate());
    }

    public function testFeedLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertEquals('fr', $feed->getLanguage());
        $this->assertEquals('fr', $feed->items[0]->getLanguage());
    }

    public function testItemId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals($parser->generateId($feed->items[0]->getUrl(), $feed->getUrl()), $feed->items[0]->getId());
    }

    public function testItemUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://www.mathieurobin.com/2013/03/chroniques-jquery-episode-108/', $feed->items[0]->getUrl());
    }

    public function testItemTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('LaFermeDuWeb : PowerTip - Des tooltips aux fonctionnalités avancées', $feed->items[1]->getTitle());
    }

    public function testItemDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(1362647700, $feed->items[1]->getDate());
    }

    public function testItemLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('fr', $feed->items[1]->getLanguage());
    }

    public function testItemAuthor()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('LaFermeDuWeb', $feed->items[1]->getAuthor());
    }

    public function testItemContent()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/planete-jquery.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<a href="http://www.lafermeduweb.net') === 0);
    }
}