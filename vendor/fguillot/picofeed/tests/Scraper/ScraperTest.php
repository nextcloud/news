<?php

namespace PicoFeed\Scraper;

use PHPUnit_Framework_TestCase;
use PicoFeed\Reader\Reader;
use PicoFeed\Config\Config;

class ScraperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group online
     */
    public function testUrlScraper()
    {
        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://theonion.com.feedsportal.com/c/34529/f/632231/s/309a7fe4/sc/20/l/0L0Stheonion0N0Carticles0Cobama0Ethrows0Eup0Eright0Ethere0Eduring0Esyria0Emeeting0H336850C/story01.htm');
        $grabber->execute();
        $this->assertTrue($grabber->hasRelevantContent());

        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://www.lemonde.fr/proche-orient/article/2013/08/30/la-france-nouvelle-plus-ancienne-alliee-des-etats-unis_3469218_3218.html');
        $grabber->execute();
        $this->assertTrue($grabber->hasRelevantContent());

        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://www.inc.com/suzanne-lucas/why-employee-turnover-is-so-costly.html');
        $grabber->execute();
        $this->assertTrue($grabber->hasRelevantContent());

        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://arstechnica.com/information-technology/2013/08/sysadmin-security-fail-nsa-finds-snowden-hijacked-officials-logins/');
        $grabber->execute();
        $this->assertTrue($grabber->hasRelevantContent());

        $grabber = new Scraper(new Config);
        $grabber->disableCandidateParser();
        $grabber->setUrl('http://linuxfr.org/news/grammalecte-correcteur-grammatical');
        $grabber->execute();
        $this->assertFalse($grabber->hasRelevantContent());
    }

    /**
     * @group online
     */
    public function testRuleParser()
    {
        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://www.egscomics.com/index.php?id=1690');
        $grabber->execute();
        $this->assertTrue($grabber->hasRelevantContent());

        $this->assertEquals('<img title="2013-08-22" src="comics/../comics/1377151029-2013-08-22.png" id="comic" border="0" />', $grabber->getRelevantContent());
    }

    /**
     * @group online
     */
    public function testGrabContentRegex()
    {
        $grabber = new Scraper(new Config);
        $grabber->setUrl('http://penny-arcade.com/comic/2015/04/13/101-part-one');
        $grabber->execute();

        $this->assertTrue($grabber->hasRelevantContent());
        $this->assertEquals('<img src="http://art.penny-arcade.com/photos/i-tBMHkzG/0/1050x10000/i-tBMHkzG-1050x10000.jpg" alt="101, Part One"/>', $grabber->getRelevantContent());

        $grabber->setUrl('http://penny-arcade.com/news/post/2015/04/15/101-part-two');
        $grabber->execute();

        $this->assertTrue($grabber->hasRelevantContent());
        $this->assertContains('101, Part Two', $grabber->getRelevantContent());
    }

    /**
     * @group online
     */
    public function testRssGrabContent()
    {
        $reader = new Reader;
        $client = $reader->download('http://www.egscomics.com/rss.php');
        $parser = $reader->getParser($client->getUrl(), $client->getContent(), $client->getEncoding());
        $parser->enableContentGrabber();
        $feed = $parser->execute();

        $this->assertTrue(is_array($feed->items));
        $this->assertTrue(strpos($feed->items[0]->content, '<img') >= 0);
    }
}
