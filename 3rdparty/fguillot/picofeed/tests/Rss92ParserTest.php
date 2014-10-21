<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Parsers/Rss92.php';

use PicoFeed\Parsers\Rss92;

class Rss92ParserTest extends PHPUnit_Framework_TestCase
{
    public function testFormatOk()
    {
        $parser = new Rss92(file_get_contents('tests/fixtures/univers_freebox.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);

        $this->assertEquals('Univers Freebox', $feed->title);
        $this->assertEquals('http://www.universfreebox.com', $feed->url);
        $this->assertEquals('http://www.universfreebox.com', $feed->id);
        $this->assertEquals(time(), $feed->date);
        $this->assertEquals(30, count($feed->items));

        $this->assertEquals('Retour de Xavier Niel sur Twitter, « sans initiative privée, pas de révolution #Born2code »', $feed->items[0]->title);
        $this->assertEquals('http://www.universfreebox.com/article20302.html', $feed->items[0]->url);
        $this->assertEquals('4e8596dc', $feed->items[0]->id);
        $this->assertEquals('', $feed->items[0]->author);
    }
}