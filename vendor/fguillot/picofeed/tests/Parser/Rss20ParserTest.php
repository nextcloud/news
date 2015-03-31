<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;
use DateTime;

class Rss20ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PicoFeed\Parser\MalformedXmlException
     */
    public function testBadInput()
    {
        $parser = new Rss20('boo');
        $parser->execute();
    }

    public function testFeedTitle()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('WordPress News', $feed->getTitle());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertEquals('PC INpact', $feed->getTitle());
    }

    public function testFeedDescription()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('WordPress News', $feed->getDescription());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Actualités Informatique', $feed->getDescription());

        $parser = new Rss20(file_get_contents('tests/fixtures/sametmax.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Deux développeurs en vadrouille qui se sortent les doigts du code', $feed->getDescription());
    }

    public function testFeedLogo()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getLogo());

        $parser = new Rss20(file_get_contents('tests/fixtures/radio-france.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://media.radiofrance-podcast.net/podcast09/RF_OMM_0000006330_ITE.jpg', $feed->getLogo());
    }

    public function testFeedUrl()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getFeedUrl());

        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'), '', 'http://example.com/feed');
        $feed = $parser->execute();
        $this->assertEquals('http://example.com/feed', $feed->getFeedUrl());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getFeedUrl());
    }

    public function testSiteUrl()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://wordpress.org/news', $feed->getSiteUrl());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://www.pcinpact.com/', $feed->getSiteUrl());
    }

    public function testFeedId()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('http://wordpress.org/news', $feed->getId());
    }

    public function testFeedDate()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1359066183, $feed->getDate()->getTimestamp());

        $parser = new Rss20(file_get_contents('tests/fixtures/fulltextrss.xml'));
        $feed = $parser->execute();
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
    }

    public function testFeedLanguage()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertEquals('en-US', $feed->getLanguage());
        $this->assertEquals('en-US', $feed->items[0]->getLanguage());

        $parser = new Rss20(file_get_contents('tests/fixtures/zoot_egkty.xml'));
        $feed = $parser->execute();
        $this->assertEquals('ur', $feed->getLanguage());
        $this->assertEquals('ur', $feed->items[0]->getLanguage());

        $parser = new Rss20(file_get_contents('tests/fixtures/ibash.ru.xml'));
        $feed = $parser->execute();
        $this->assertEquals('ru', $feed->getLanguage());
        $this->assertEquals('ru', $feed->items[0]->getLanguage());
    }

    public function testItemId()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('de679f14fc4774f0d6dfe73c3f8c8368ab85da18addf101a2af8c32ac6320f9f', $feed->items[0]->getId());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(hash('sha256', '78872'), $feed->items[0]->getId());

        $parser = new Rss20(file_get_contents('tests/fixtures/fulltextrss.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(hash('sha256', 'http://www.numerama.com/magazine/25669-brevets-un-juge-doute-de-la-bonne-volonte-de-google-et-apple.html'), $feed->items[0]->getId());

        $parser = new Rss20(file_get_contents('tests/fixtures/debug_show.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(hash('sha256', '38DC2FF1-4207-4C04-93F3-2DAFB0E559D9'), $feed->items[1]->getId());
        $this->assertEquals(hash('sha256', '3FA03A63-BEA2-4199-A1E4-D2963845F3F6'), $feed->items[2]->getId());
        $this->assertEquals($feed->items[1]->getUrl(), $feed->items[2]->getUrl());
        $this->assertNotEquals($feed->items[1]->getId(), $feed->items[2]->getId());
    }

    public function testItemUrl()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://wordpress.org/news/2013/01/wordpress-3-5-1/', $feed->items[0]->getUrl());

        $parser = new Rss20(file_get_contents('tests/fixtures/pcinpact.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://www.pcinpact.com/breve/78872-la-dcri-purge-wikipedia-par-menace-bel-effet-streisand-a-cle.htm?utm_source=PCi_RSS_Feed&utm_medium=news&utm_campaign=pcinpact', $feed->items[0]->getUrl());
    }

    public function testItemTitle()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('2012: A Look Back', $feed->items[1]->getTitle());

        $parser = new Rss20(file_get_contents('tests/fixtures/womensweardaily.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('They Are Wearing: Frieze London Photo by Marcus Dawes', $feed->items[3]->getTitle());
    }

    public function testItemDate()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('2013-01-01', $feed->items[1]->getDate()->format('Y-m-d'));

        $parser = new Rss20(file_get_contents('tests/fixtures/fulltextrss.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('2013-04-12', $feed->items[0]->getDate()->format('Y-m-d'));
    }

    public function testItemLanguage()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('en-US', $feed->items[1]->getLanguage());
    }

    public function testItemAuthor()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('Jen Mylo', $feed->items[1]->getAuthor());

        $parser = new Rss20(file_get_contents('tests/fixtures/rss2sample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('webmaster@example.com', $feed->items[2]->getAuthor());
    }

    public function testItemContent()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/allgemeine-zeitung.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(
            '<p>OBER-FLÖRSHEIM - (red). Sein erheblich beschädigtes Fahrzeug hat ein Fahrer in der Nacht von Donnerstag auf Freitag mitten auf der Ober-Flörsheimer Hauptstraße stehen lassen. Beim Verlassen des Autos in Richtung ...</p>',
            $feed->items[2]->getContent()
        );

        $parser = new Rss20(file_get_contents('tests/fixtures/rss20.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<p>Another year is coming') === 0);

        $parser = new Rss20(file_get_contents('tests/fixtures/rss2sample.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[1]->getContent(), '<p>Sky watchers in Europe') === 0);

        $parser = new Rss20(file_get_contents('tests/fixtures/ibash.ru.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertTrue(strpos($feed->items[0]->getContent(), '<p>Хабр, обсуждение фейлов на работе: reaferon: Интернет') === 0);
    }

    public function testItemEnclosure()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/rue89.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://rue89.feedsportal.com/c/33822/f/608948/e/1/s/2a687021/l/0L0Srue890N0Csites0Cnews0Cfiles0Cstyles0Cmosaic0Cpublic0Czapnet0Cthumbnail0Isquare0C20A130C0A40Ccahuzac0I10Bpng/cahuzac_1.png', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('image/png', $feed->items[0]->getEnclosureType());
    }

    public function testFeedsReportedAsNotWorking()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/biertaucher.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(177, count($feed->items));

        $parser = new Rss20(file_get_contents('tests/fixtures/radio-france.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals(52, count($feed->items));

        $parser = new Rss20(file_get_contents('tests/fixtures/fanboys.fm_episodes.all.mp3.rss'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);

        $parser = new Rss20(file_get_contents('tests/fixtures/geekstammtisch.de_episodes.mp3.rss'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
        $this->assertEquals('http://geekstammtisch.de/#GST001', $feed->items[1]->getUrl());

        $parser = new Rss20(file_get_contents('tests/fixtures/lincoln_loop.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);

        $parser = new Rss20(file_get_contents('tests/fixtures/next_inpact_full.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);

        $parser = new Rss20(file_get_contents('tests/fixtures/jeux-linux.fr.xml'));
        $feed = $parser->execute();
        $this->assertNotEmpty($feed->items);
    }
}
