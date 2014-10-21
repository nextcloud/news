<?php

require_once 'lib/PicoFeed/PicoFeed.php';

use PicoFeed\Filter;
use PicoFeed\Config;

class FilterTest extends PHPUnit_Framework_TestCase
{
    public function testStripHeadTag()
    {
        $input = '<html><head><title>test</title></head><body><h1>boo</h1></body>';
        $expected = '<html><body><h1>boo</h1></body>';
        $this->assertEquals($expected, Filter::stripHeadTags($input));

        $input = file_get_contents('tests/fixtures/html_page.html');
        $expected = file_get_contents('tests/fixtures/html_head_stripped_page.html');
        $this->assertEquals($expected, Filter::stripHeadTags($input));
    }

    public function testStripXmlTag()
    {
        $data = file_get_contents('tests/fixtures/jeux-linux.fr.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/ezrss.it');
        $this->assertEquals('<!DOC', substr(Filter::stripXmlTag($data), 0, 5));

        $data = file_get_contents('tests/fixtures/fulltextrss.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/sametmax.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/grotte_barbu.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/ibash.ru.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/pcinpact.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/resorts.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/rue89.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/cercle.psy.xml');
        $this->assertEquals('<rss', substr(Filter::stripXmlTag($data), 0, 4));

        $data = file_get_contents('tests/fixtures/lagrange.xml');
        $this->assertEquals('<feed', substr(Filter::stripXmlTag($data), 0, 5));

        $data = file_get_contents('tests/fixtures/atom.xml');
        $this->assertEquals('<feed', substr(trim(Filter::stripXmlTag($data)), 0, 5));

        $data = file_get_contents('tests/fixtures/atomsample.xml');
        $this->assertEquals('<feed', substr(trim(Filter::stripXmlTag($data)), 0, 5));

        $data = file_get_contents('tests/fixtures/planete-jquery.xml');
        $this->assertEquals('<rdf:RDF', trim(substr(trim(Filter::stripXmlTag($data)), 0, 8)));
    }

    public function testOverrideFilters()
    {
        $data = '<iframe src="http://www.kickstarter.com/projects/lefnire/habitrpg-mobile/widget/video.html" height="480" width="640" frameborder="0"></iframe>';

        $f = Filter::html($data, 'http://blabla');
        $f->attribute->setIframeWhitelist(array('http://www.kickstarter.com'));
        $this->assertEquals($data, $f->execute());

        $data = '<iframe src="http://www.youtube.com/bla" height="480" width="640" frameborder="0"></iframe>';

        $f = Filter::html($data, 'http://blabla');
        $f->attribute->setIframeWhitelist(array('http://www.kickstarter.com'));
        $this->assertEmpty($f->execute());

        $config = new Config;
        $config->setFilterWhitelistedTags(array('p' => array('title')));

        $f = Filter::html('<p>Test<strong>boo</strong></p>', 'http://blabla');
        $f->setConfig($config);
        $this->assertEquals('<p>Testboo</p>', $f->execute());
    }
}