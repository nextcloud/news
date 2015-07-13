<?php
namespace PicoFeed\Filter;

use PHPUnit_Framework_TestCase;

use PicoFeed\Config\Config;


class FilterTest extends PHPUnit_Framework_TestCase
{
    public function testStripHeadTag()
    {
        $input = '<html><head><title>test</title></head><body><h1>boo</h1></body>';
        $expected = '<html><body><h1>boo</h1></body>';
        $this->assertEquals($expected, Filter::stripHeadTags($input));

        $input = file_get_contents('tests/fixtures/html4_page.html');
        $expected = file_get_contents('tests/fixtures/html4_head_stripped_page.html');
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

        $data = file_get_contents('tests/fixtures/googleblog.xml');
        $this->assertEquals('<feed', substr(trim(Filter::stripXmlTag($data)), 0, 5));

        $data = file_get_contents('tests/fixtures/atomsample.xml');
        $this->assertEquals('<feed', substr(trim(Filter::stripXmlTag($data)), 0, 5));

        $data = file_get_contents('tests/fixtures/planete-jquery.xml');
        $this->assertEquals('<rdf:RDF', trim(substr(trim(Filter::stripXmlTag($data)), 0, 8)));
    }

    public function testOverrideFilters()
    {
        $data = '<iframe src="http://www.kickstarter.com/projects/lefnire/habitrpg-mobile/widget/video.html" height="480" width="640" frameborder="0"></iframe>';
        $expected = '<iframe src="https://www.kickstarter.com/projects/lefnire/habitrpg-mobile/widget/video.html" height="480" width="640" frameborder="0"></iframe>';

        $f = Filter::html($data, 'http://blabla');
        $f->attribute->setIframeWhitelist(array('http://www.kickstarter.com'));
        $this->assertEquals($expected, $f->execute());

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

    public function testNormalizeData()
    {
        // invalid data link escape control character
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random\x10 text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random&#x10; text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random&#16; text</xml>"));

        // invalid unit seperator control character (lower and upper case)
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random\x1f text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random\x1F text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random&#x1f; text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random&#x1F; text</xml>"));
        $this->assertEquals('<xml>random text</xml>', Filter::normalizeData("<xml>random&#31; text</xml>"));

        /*
         * Do not test invalid multibyte characters. The output depends on php
         * version and character.
         *
         * php 5.3: always null
         * php >5.3: sometime null, sometimes the stripped string
         */

        // invalid backspace control character + valid multibyte character
        $this->assertEquals('<xml>“random“ text</xml>', Filter::normalizeData("<xml>\xe2\x80\x9crandom\xe2\x80\x9c\x08 text</xml>"));
        $this->assertEquals('<xml>&#x201C;random&#x201C; text</xml>', Filter::normalizeData("<xml>&#x201C;random&#x201C;&#x08; text</xml>"));
        $this->assertEquals('<xml>&#8220;random&#8220; text</xml>', Filter::normalizeData("<xml>&#8220;random&#8220;&#08; text</xml>"));

        // do not convert valid entities to utf-8 character
        $this->assertEquals('<xml attribute="&#34;value&#34;">random text</xml>', Filter::normalizeData('<xml attribute="&#34;value&#34;">random text</xml>'));
        $this->assertEquals('<xml attribute="&#x22;value&#x22;">random text</xml>', Filter::normalizeData('<xml attribute="&#x22;value&#x22;">random text</xml>'));
    }
}