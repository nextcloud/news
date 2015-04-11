<?php

namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;
use PicoFeed\Reader\Reader;
use PicoFeed\Config\Config;

class GrabberTest extends PHPUnit_Framework_TestCase
{
    public function testGetRulesFolders()
    {
        // No custom path
        $grabber = new Grabber('');
        $dirs = $grabber->getRulesFolders();
        $this->assertNotEmpty($dirs);
        $this->assertCount(1, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);

        // Custom path
        $config = new Config;
        $config->setGrabberRulesFolder('/foobar/rules');

        $grabber = new Grabber('');
        $grabber->setConfig($config);

        $dirs = $grabber->getRulesFolders();

        $this->assertNotEmpty($dirs);
        $this->assertCount(2, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);
        $this->assertEquals('/foobar/rules', $dirs[1]);

        // No custom path with empty config object
        $grabber = new Grabber('');
        $grabber->setConfig(new Config);

        $dirs = $grabber->getRulesFolders();

        $this->assertNotEmpty($dirs);
        $this->assertCount(1, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);
    }

    public function testLoadRuleFile()
    {
        $grabber = new Grabber('');
        $dirs = $grabber->getRulesFolders();

        $this->assertEmpty($grabber->loadRuleFile($dirs[0], array('test')));
        $this->assertNotEmpty($grabber->loadRuleFile($dirs[0], array('test', 'xkcd.com')));
    }

    public function testGetRulesFileList()
    {
        $grabber = new Grabber('');
        $this->assertEquals(
            array('www.google.ca', 'google.ca', '.google.ca', 'www'),
            $grabber->getRulesFileList('www.google.ca')
        );

        $grabber = new Grabber('');
        $this->assertEquals(
            array('google.ca', '.google.ca', 'google'),
            $grabber->getRulesFileList('google.ca')
        );

        $grabber = new Grabber('');
        $this->assertEquals(
            array('a.b.c.d', 'b.c.d', '.b.c.d', 'a'),
            $grabber->getRulesFileList('a.b.c.d')
        );

        $grabber = new Grabber('');
        $this->assertEquals(
            array('localhost'),
            $grabber->getRulesFileList('localhost')
        );
    }

    public function testGetRules()
    {
        $grabber = new Grabber('http://www.egscomics.com/index.php?id=1690');
        $this->assertNotEmpty($grabber->getRules());

        $grabber = new Grabber('http://localhost/foobar');
        $this->assertEmpty($grabber->getRules());
    }

    /**
     * @group online
     */
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

    /**
     * @group online
     */
    public function testGetRules_afterRedirection()
    {
        $grabber = new Grabber('http://rss.feedsportal.com/c/629/f/502199/s/422f8c8a/sc/44/l/0L0S0A1net0N0Ceditorial0C640A3130Cces0E20A150Eimprimer0Eune0Epizza0Eet0Edes0Ebiscuits0Evideo0C0T0Dxtor0FRSS0E16/story01.htm');
        $grabber->download();
        $this->assertTrue(is_array($grabber->getRules()));
    }

    /**
     * @group online
     */
    public function testGrabContent()
    {
        $grabber = new Grabber('http://www.egscomics.com/index.php?id=1690');
        $grabber->download();
        $this->assertTrue($grabber->parse());

        $this->assertEquals('<img title="2013-08-22" src="comics/../comics/1377151029-2013-08-22.png" id="comic" border="0" />', $grabber->getContent());
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
