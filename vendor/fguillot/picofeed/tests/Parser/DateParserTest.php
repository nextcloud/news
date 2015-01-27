<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class DateParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseDate()
    {
        $parser = new DateParser;

        date_default_timezone_set('UTC');

        $this->assertEquals(1359066183, $parser->getTimestamp('Thu, 24 Jan 2013 22:23:03 +0000'));
        $this->assertEquals(1362992761, $parser->getTimestamp('2013-03-11T09:06:01+00:00'));
        $this->assertEquals(1363752990, $parser->getTimestamp('2013-03-20T04:16:30+00:00'));
        $this->assertEquals(1359066183, $parser->getTimestamp('Thu, 24 Jan 2013 22:23:03 +0000'));
        $this->assertEquals(1380929699, $parser->getTimestamp('Sat, 04 Oct 2013 02:34:59 +0300'));
        $this->assertEquals(1054633161, $parser->getTimestamp('Tue, 03 Jun 2003 09:39:21 GMT'));
        $this->assertEquals(1071340202, $parser->getTimestamp('2003-12-13T18:30:02Z'));
        $this->assertEquals(1364234797, $parser->getTimestamp('Mon, 25 Mar 2013 19:06:37 +0100'));
        $this->assertEquals(1360054941, $parser->getTimestamp('2013-02-05T09:02:21.880-08:00'));
        $this->assertEquals(1286834400, $parser->getTimestamp('Tue, 12 Oct 2010 00:00:00 IST'));
        $this->assertEquals('2014-12-15 19:49', date('Y-m-d H:i', $parser->getTimestamp('15 Dec 2014 19:49:07 +0100')));
        $this->assertEquals('2012-05-15', date('Y-m-d', $parser->getTimestamp('Tue, 15 May 2012 24:05:00 UTC')));
        $this->assertEquals('2013-09-12', date('Y-m-d', $parser->getTimestamp('Thu, 12 Sep 2013 7:00:00 UTC')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->getTimestamp('01.31.2012')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->getTimestamp('01/31/2012')));
        $this->assertEquals('2012-01-31', date('Y-m-d', $parser->getTimestamp('2012-01-31')));
        $this->assertEquals('2010-02-24', date('Y-m-d', $parser->getTimestamp('2010-02-245T15:27:52Z')));
        $this->assertEquals('2010-08-20', date('Y-m-d', $parser->getTimestamp('2010-08-20Thh:08:ssZ')));
        $this->assertEquals(1288648057, $parser->getTimestamp('Mon, 01 Nov 2010 21:47:37 UT'));
        $this->assertEquals(1346069615, $parser->getTimestamp('Mon Aug 27 2012 12:13:35 GMT-0700 (PDT)'));
        $this->assertEquals(time(), $parser->getTimestamp('Tue, 3 Febuary 2010 00:00:00 IST'));
        $this->assertEquals(time(), $parser->getTimestamp('############# EST'));
        $this->assertEquals(time(), $parser->getTimestamp('Wed, 30 Nov -0001 00:00:00 +0000'));
        $this->assertEquals(time(), $parser->getTimestamp('čet, 24 maj 2012 00:00:00'));
        $this->assertEquals(time(), $parser->getTimestamp('-0-0T::Z'));
        $this->assertEquals(time(), $parser->getTimestamp('Wed, 18 2012'));
        $this->assertEquals(time(), $parser->getTimestamp("'2009-09-30 CDT16:09:54"));
        $this->assertEquals(time(), $parser->getTimestamp('ary 8 Jan 2013 00:00:00 GMT'));
        $this->assertEquals(time(), $parser->getTimestamp('Sat, 11 00:00:01 GMT'));
        $this->assertEquals(1370631743, $parser->getTimestamp('Fri Jun 07 2013 19:02:23 GMT+0000 (UTC)'));
        $this->assertEquals(1377412225, $parser->getTimestamp('25/08/2013 06:30:25 م'));
        $this->assertEquals(time(), $parser->getTimestamp('+0400'));
    }
}
