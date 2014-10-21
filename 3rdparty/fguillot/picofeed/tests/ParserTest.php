<?php

require_once 'lib/PicoFeed/PicoFeed.php';
require_once 'lib/PicoFeed/Parsers/Rss20.php';

use PicoFeed\XmlParser;
use PicoFeed\Parser;
use PicoFeed\Parsers\Rss20;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseDate()
    {
        $parser = new Rss20('');

        date_default_timezone_set('UTC');

        $this->assertEquals(1359066183, $parser->parseDate('Thu, 24 Jan 2013 22:23:03 +0000'));
        $this->assertEquals(1362992761, $parser->parseDate('2013-03-11T09:06:01+00:00'));
        $this->assertEquals(1363752990, $parser->parseDate('2013-03-20T04:16:30+00:00'));
        $this->assertEquals(1359066183, $parser->parseDate('Thu, 24 Jan 2013 22:23:03 +0000'));
        $this->assertEquals(1380929699, $parser->parseDate('Sat, 04 Oct 2013 02:34:59 +0300'));
        $this->assertEquals(1054633161, $parser->parseDate('Tue, 03 Jun 2003 09:39:21 GMT'));
        $this->assertEquals(1071340202, $parser->parseDate('2003-12-13T18:30:02Z'));
        $this->assertEquals(1364234797, $parser->parseDate('Mon, 25 Mar 2013 19:06:37 +0100'));
        $this->assertEquals(1360054941, $parser->parseDate('2013-02-05T09:02:21.880-08:00'));
        $this->assertEquals(1286834400, $parser->parseDate('Tue, 12 Oct 2010 00:00:00 IST'));
        $this->assertEquals('2012-05-15', date('Y-m-d', $parser->parseDate('Tue, 15 May 2012 24:05:00 UTC')));
        $this->assertEquals('2013-09-12', date('Y-m-d', $parser->parseDate('Thu, 12 Sep 2013 7:00:00 UTC')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->parseDate('01.31.2012')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->parseDate('01/31/2012')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->parseDate('2012-01-31')));
        $this->assertEquals('2010-02-24', date('Y-m-d', $parser->parseDate('2010-02-245T15:27:52Z')));
        $this->assertEquals('2010-08-20', date('Y-m-d', $parser->parseDate('2010-08-20Thh:08:ssZ')));
        $this->assertEquals(1288648057, $parser->parseDate('Mon, 01 Nov 2010 21:47:37 UT'));
        $this->assertEquals(1346069615, $parser->parseDate('Mon Aug 27 2012 12:13:35 GMT-0700 (PDT)'));
        $this->assertEquals(time(), $parser->parseDate('Tue, 3 Febuary 2010 00:00:00 IST'));
        $this->assertEquals(time(), $parser->parseDate('############# EST'));
        $this->assertEquals(time(), $parser->parseDate('Wed, 30 Nov -0001 00:00:00 +0000'));
        $this->assertEquals(time(), $parser->parseDate('čet, 24 maj 2012 00:00:00'));
        $this->assertEquals(time(), $parser->parseDate('-0-0T::Z'));
        $this->assertEquals(time(), $parser->parseDate('Wed, 18 2012'));
        $this->assertEquals(time(), $parser->parseDate("'2009-09-30 CDT16:09:54"));
        $this->assertEquals(time(), $parser->parseDate('ary 8 Jan 2013 00:00:00 GMT'));
        $this->assertEquals(time(), $parser->parseDate('Sat, 11 00:00:01 GMT'));
        $this->assertEquals(1370631743, $parser->parseDate('Fri Jun 07 2013 19:02:23 GMT+0000 (UTC)'));
        $this->assertEquals(1377412225, $parser->parseDate('25/08/2013 06:30:25 م'));
        $this->assertEquals(time(), $parser->parseDate('+0400'));
    }

/*
    public function testNormalizeData()
    {
        $parser = new Rss20('');

        $data = '<title> Police &#039;Like&#039; Wanted Suspect&#039;s Facebook Post</title>
            <link rel="alternate" type="text/html" href="http://www.huffingtonpost.com/huff-wires/20140121/us-odd--police-like-facebook-post/?utm_hp_ref=travel&ir=travel" />
        <id>http://www.huffingtonpost.com/2014/01/22/anthony-lescowitch-facebook_n_4643239.html</id>
        <truc href="blabla &amp;"/>';

        $result = $parser->replaceEntityAttribute($data);

        $expected = '<title> Police &#039;Like&#039; Wanted Suspect&#039;s Facebook Post</title>
            <link rel="alternate" type="text/html" href="http://www.huffingtonpost.com/huff-wires/20140121/us-odd--police-like-facebook-post/?utm_hp_ref=travel&amp;ir=travel" />
        <id>http://www.huffingtonpost.com/2014/01/22/anthony-lescowitch-facebook_n_4643239.html</id>
        <truc href="blabla &amp;"/>';

        $this->assertEquals($expected, $result);
    }
*/

    public function testChangeHashAlgo()
    {
        $parser = new Rss20('');
        $this->assertEquals('9e83486d', $parser->generateId('a', 'b'));

        $parser->setHashAlgo('sha1');
        $this->assertEquals('da23614e02469a0d7c7bd1bdab5c9c474b1904dc', $parser->generateId('a', 'b'));
    }

    public function testLangRTL()
    {
        $this->assertFalse(Parser::isLanguageRTL('fr-FR'));
        $this->assertTrue(Parser::isLanguageRTL('ur'));
        $this->assertTrue(Parser::isLanguageRTL('syr-**'));
        $this->assertFalse(Parser::isLanguageRTL('ru'));
    }

    public function testNamespaceValue()
    {
        $xml = XmlParser::getSimpleXml(file_get_contents('tests/fixtures/rue89.xml'));
        $this->assertNotFalse($xml);
        $namespaces = $xml->getNamespaces(true);

        $parser = new Rss20('');
        $this->assertEquals('Blandine Grosjean', XmlParser::getNamespaceValue($xml->channel->item[0], $namespaces, 'creator'));
        $this->assertEquals('Pierre-Carl Langlais', XmlParser::getNamespaceValue($xml->channel->item[1], $namespaces, 'creator'));
    }
}
