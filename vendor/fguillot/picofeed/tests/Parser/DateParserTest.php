<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class DateParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseDate()
    {
        $parser = new DateParser;

        date_default_timezone_set('UTC');

        $this->assertEquals('2013-04-12', $parser->getDateTime('Fri, 12 Apr 2013 15:38:15 +0000')->format('Y-m-d'));
        $this->assertEquals(1359066183, $parser->getDateTime('Thu, 24 Jan 2013 22:23:03 +0000')->getTimestamp(), '', 1);
        $this->assertEquals(1362992761, $parser->getDateTime('2013-03-11T09:06:01+00:00')->getTimestamp(), '', 1);
        $this->assertEquals(1363752990, $parser->getDateTime('2013-03-20T04:16:30+00:00')->getTimestamp(), '', 1);
        $this->assertEquals(1359066183, $parser->getDateTime('Thu, 24 Jan 2013 22:23:03 +0000')->getTimestamp(), '', 1);
        $this->assertEquals(1380929699, $parser->getDateTime('Sat, 04 Oct 2013 02:34:59 +0300')->getTimestamp(), '', 1);
        $this->assertEquals(1054633161, $parser->getDateTime('Tue, 03 Jun 2003 09:39:21 GMT')->getTimestamp(), '', 1);
        $this->assertEquals(1071340202, $parser->getDateTime('2003-12-13T18:30:02Z')->getTimestamp(), '', 1);
        $this->assertEquals(1364234797, $parser->getDateTime('Mon, 25 Mar 2013 19:06:37 +0100')->getTimestamp(), '', 1);
        $this->assertEquals(1360054941, $parser->getDateTime('2013-02-05T09:02:21.880-08:00')->getTimestamp(), '', 1);
        $this->assertEquals(1286834400, $parser->getDateTime('Tue, 12 Oct 2010 00:00:00 IST')->getTimestamp(), '', 1);
        $this->assertEquals('2014-12-15 19:49', $parser->getDateTime('15 Dec 2014 19:49:07 +0100')->format('Y-m-d H:i'));
        $this->assertEquals('2012-05-15', $parser->getDateTime('Tue, 15 May 2012 24:05:00 UTC')->format('Y-m-d'));
        $this->assertEquals('2013-09-12', $parser->getDateTime('Thu, 12 Sep 2013 7:00:00 UTC')->format('Y-m-d'));
        $this->assertEquals('2012-01-31', $parser->getDateTime('01.31.2012')->format('Y-m-d'));
        $this->assertEquals('2012-01-31', $parser->getDateTime('01/31/2012')->format('Y-m-d'));
        $this->assertEquals('2012-01-31', $parser->getDateTime('2012-01-31')->format('Y-m-d'));
        $this->assertEquals('2010-02-24', $parser->getDateTime('2010-02-245T15:27:52Z')->format('Y-m-d'));
        $this->assertEquals('2010-08-20', $parser->getDateTime('2010-08-20Thh:08:ssZ')->format('Y-m-d'));
        $this->assertEquals(1288648057, $parser->getDateTime('Mon, 01 Nov 2010 21:47:37 UT')->getTimestamp(), '', 1);
        $this->assertEquals(1346069615, $parser->getDateTime('Mon Aug 27 2012 12:13:35 GMT-0700 (PDT)')->getTimestamp(), '', 1);
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('Tue, 3 Febuary 2010 00:00:00 IST'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('############# EST'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('Wed, 30 Nov -0001 00:00:00 +0000'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('čet, 24 maj 2012 00:00:00'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('-0-0T::Z'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('Wed, 18 2012'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime("'2009-09-30 CDT16:09:54"));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('ary 8 Jan 2013 00:00:00 GMT'));
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('Sat, 11 00:00:01 GMT'));
        $this->assertEquals(1370631743, $parser->getDateTime('Fri Jun 07 2013 19:02:23 GMT+0000 (UTC)')->getTimestamp(), '', 1);
        $this->assertEquals(1377412225, $parser->getDateTime('25/08/2013 06:30:25 م')->getTimestamp(), '', 1);
        $this->assertEquals($parser->getCurrentDateTime(), $parser->getDateTime('+0400'));
    }
}
