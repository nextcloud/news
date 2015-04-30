<?php

namespace PicoFeed\Scraper;

use PHPUnit_Framework_TestCase;
use PicoFeed\Config\Config;

class RuleLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testGetRulesFolders()
    {
        // No custom path
        $loader = new RuleLoader(new Config);
        $dirs = $loader->getRulesFolders();
        $this->assertNotEmpty($dirs);
        $this->assertCount(1, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);

        // Custom path
        $config = new Config;
        $config->setGrabberRulesFolder('/foobar/rules');

        $loader = new RuleLoader($config);

        $dirs = $loader->getRulesFolders();

        $this->assertNotEmpty($dirs);
        $this->assertCount(2, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);
        $this->assertEquals('/foobar/rules', $dirs[1]);

        // No custom path with empty config object
        $loader = new RuleLoader(new Config);

        $dirs = $loader->getRulesFolders();

        $this->assertNotEmpty($dirs);
        $this->assertCount(1, $dirs);
        $this->assertTrue(strpos($dirs[0], '/../Rules') !== false);
    }

    public function testLoadRuleFile()
    {
        $loader = new RuleLoader(new Config);
        $dirs = $loader->getRulesFolders();

        $this->assertEmpty($loader->loadRuleFile($dirs[0], array('test')));
        $this->assertNotEmpty($loader->loadRuleFile($dirs[0], array('test', 'xkcd.com')));
    }

    public function testGetRulesFileList()
    {
        $loader = new RuleLoader(new Config);
        $this->assertEquals(
            array('www.google.ca', 'google.ca', '.google.ca', 'www'),
            $loader->getRulesFileList('www.google.ca')
        );

        $loader = new RuleLoader(new Config);
        $this->assertEquals(
            array('google.ca', '.google.ca', 'google'),
            $loader->getRulesFileList('google.ca')
        );

        $loader = new RuleLoader(new Config);
        $this->assertEquals(
            array('a.b.c.d', 'b.c.d', '.b.c.d', 'a'),
            $loader->getRulesFileList('a.b.c.d')
        );

        $loader = new RuleLoader(new Config);
        $this->assertEquals(
            array('localhost'),
            $loader->getRulesFileList('localhost')
        );
    }

    public function testGetRules()
    {
        $loader = new RuleLoader(new Config);
        $this->assertNotEmpty($loader->getRules('http://www.egscomics.com/index.php?id=1690'));

        $loader = new RuleLoader(new Config);
        $this->assertEmpty($loader->getRules('http://localhost/foobar'));
    }
}
