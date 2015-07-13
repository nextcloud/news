<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class ItemTest extends PHPUnit_Framework_TestCase
{
    public function testLangRTL()
    {
        $item = new Item;
        $item->language = 'fr_FR';
        $this->assertFalse($item->isRTL());

        $item->language = 'ur';
        $this->assertTrue($item->isRTL());

        $item->language = 'syr-**';
        $this->assertTrue($item->isRTL());

        $item->language = 'ru';
        $this->assertFalse($item->isRTL());
    }

    public function testGetTag()
    {
        $parser = new Rss20(file_get_contents('tests/fixtures/podbean.xml'));
        $feed = $parser->execute();
        $this->assertEquals(array('http://aroundthebloc.podbean.com/e/s03e11-finding-nemo-rocco/'), $feed->items[0]->getTag('guid'));
        $this->assertEquals(array('false'),  $feed->items[0]->getTag('guid', 'isPermaLink'));
        $this->assertEquals(array('http://aroundthebloc.podbean.com/mf/web/28bcnk/ATBLogo-BlackBackground.png'),  $feed->items[0]->getTag('media:content', 'url'));
        $this->assertEquals(array('http://aroundthebloc.podbean.com/e/s03e11-finding-nemo-rocco/feed/'),  $feed->items[0]->getTag('wfw:commentRss'));
        $this->assertEquals(array(),  $feed->items[0]->getTag('wfw:notExistent'));
        $this->assertCount(7, $feed->items[0]->getTag('itunes:*'));
    }
}
