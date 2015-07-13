<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testChangeHashAlgo()
    {
        $parser = new Rss20('');
        $this->assertEquals('fb8e20fc2e4c3f248c60c39bd652f3c1347298bb977b8b4d5903b85055620603', $parser->generateId('a', 'b'));

        $parser->setHashAlgo('sha1');
        $this->assertEquals('da23614e02469a0d7c7bd1bdab5c9c474b1904dc', $parser->generateId('a', 'b'));
    }

    public function testLangRTL()
    {
        $this->assertFalse(Parser::isLanguageRTL('fr-FR'));
        $this->assertTrue(Parser::isLanguageRTL('ur'));
        $this->assertTrue(Parser::isLanguageRTL('syr-**'));
        $this->assertFalse(Parser::isLanguageRTL('ru'));
    }

    public function testFeedsWithInvalidCharacters()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/lincoln_loop.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);

        $parser = new Rss20(file_get_contents('tests/fixtures/next_inpact_full.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
    }

    public function testFeedEncodingsAreSupported()
    {
        // windows-1251
        $parser = new Rss20(file_get_contents('tests/fixtures/ibash.ru.xml'));
        $feed = $parser->execute();
        $this->assertEquals('<p>Хабр, обсуждение фейлов на работе: reaferon: Интернет-магазин с оборотом более 1 млн. в месяц. При округлении цены до двух знаков после запятой: $price = round($price,2); была допущена досадная опечатка $price = rand($price,2);</p>', $feed->items[0]->getContent());

        // CP1251
        $parser = new Rss20(file_get_contents('tests/fixtures/xakep.ru.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Bug Bounty — другая сторона медали', $feed->items[23]->title);
        $this->assertEquals('<p>Бывший директор АНБ, генерал Майкл Хэйден снова показал себя во всей красе.</p>', $feed->items[0]->getContent());
    }

    public function testXMLTagStrippingIsUsed()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/jeux-linux.fr.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
    }

    public function testHTTPEncodingFallbackIsUsed()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/cercle.psy.xml'), 'iso-8859-1');
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
    }

    public function testFeedURLFallbackIsUsed()
    {
        $parser = new Atom(file_get_contents('tests/fixtures/atom_empty_feed.xml'), '', 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $feed = $parser->execute();
        $this->assertEquals('https://feeds.wikipedia.org/category/Russian-language_literature.xml', $feed->getFeedUrl());
    }
}
