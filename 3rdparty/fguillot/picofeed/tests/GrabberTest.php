<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Grabber.php';

use PicoFeed\Reader;
use PicoFeed\Grabber;
use PicoFeed\Logging;

class GrabberTest extends PHPUnit_Framework_TestCase
{
    public function testGrabContentWithCandidates()
    {
        $grabber = new Grabber('http://theonion.com.feedsportal.com/c/34529/f/632231/s/309a7fe4/sc/20/l/0L0Stheonion0N0Carticles0Cobama0Ethrows0Eup0Eright0Ethere0Eduring0Esyria0Emeeting0H336850C/story01.htm');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $grabber = new Grabber('http://www.lemonde.fr/proche-orient/article/2013/08/30/la-france-nouvelle-plus-ancienne-alliee-des-etats-unis_3469218_3218.html');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $grabber = new Grabber('http://www.rue89.com/2013/08/30/faisait-boris-boillon-ex-sarko-boy-350-000-euros-gare-nord-245315');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $grabber = new Grabber('http://www.inc.com/suzanne-lucas/why-employee-turnover-is-so-costly.html');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $grabber = new Grabber('http://arstechnica.com/information-technology/2013/08/sysadmin-security-fail-nsa-finds-snowden-hijacked-officials-logins/');
        $grabber->download();
        $this->assertTrue($grabber->parse());
    }

    public function testGetRules()
    {
        $grabber = new Grabber('http://www.egscomics.com/index.php?id=1690');
        $this->assertTrue(is_array($grabber->getRules()));
    }

    public function testGrabContent()
    {
        $grabber = new Grabber('http://www.egscomics.com/index.php?id=1690');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $this->assertEquals('<img title="2013-08-22" src="comics/../comics/1377151029-2013-08-22.png" id="comic" border="0" />', $grabber->getContent());
    }

    public function testRssGrabContent()
    {
        $reader = new Reader;
        $reader->download('http://www.egscomics.com/rss.php');

        $parser = $reader->getParser();
        $this->assertTrue($parser !== false);

        $parser->grabber = true;
        $feed = $parser->execute();

        $this->assertTrue(is_array($feed->items));
        $this->assertTrue(strpos($feed->items[0]->content, '<img') >= 0);
    }
}
