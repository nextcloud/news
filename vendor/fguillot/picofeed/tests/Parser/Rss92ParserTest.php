<?php
namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;


class Rss92ParserTest extends PHPUnit_Framework_TestCase
{
    public function testFormatOk()
    {
        $parser = new Rss92(file_get_contents('tests/fixtures/univers_freebox.xml'));
        $feed = $parser->execute();

        $this->assertNotFalse($feed);
        $this->assertNotEmpty($feed->items);

        $this->assertEquals('Univers Freebox', $feed->getTitle());
        $this->assertEquals('', $feed->getFeedUrl());
        $this->assertEquals('http://www.universfreebox.com/', $feed->getSiteUrl());
        $this->assertEquals('http://www.universfreebox.com/', $feed->getId());
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
        $this->assertEquals(30, count($feed->items));

        $this->assertEquals('Retour de Xavier Niel sur Twitter, « sans initiative privée, pas de révolution #Born2code »', $feed->items[0]->title);
        $this->assertEquals('http://www.universfreebox.com/article20302.html', $feed->items[0]->getUrl());
        $this->assertEquals('ad23a45af194cc46d5151a9a062c5841b03405e456595c30b742d827e08af2e0', $feed->items[0]->getId());
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }
}