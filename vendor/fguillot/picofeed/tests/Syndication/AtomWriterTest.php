<?php
namespace PicoFeed\Syndication;

use PHPUnit_Framework_TestCase;


class AtomWriterTest extends PHPUnit_Framework_TestCase
{
    public function testWriter()
    {
        $writer = new Atom();
        $writer->title = 'My site';
        $writer->site_url = 'http://boo/';
        $writer->feed_url = 'http://boo/feed.atom';
        $writer->author = array(
            'name' => 'Me',
            'url' => 'http://me',
            'email' => 'me@here'
        );

        $writer->items[] = array(
            'title' => 'My article 1',
            'updated' => strtotime('-2 days'),
            'url' => 'http://foo/bar',
            'summary' => 'Super summary',
            'content' => '<p>content</p>'
        );

        $writer->items[] = array(
            'title' => 'My article 2',
            'updated' => strtotime('-1 day'),
            'url' => 'http://foo/bar2',
            'summary' => 'Super summary 2',
            'content' => '<p>content 2 &nbsp; &copy; 2015</p>',
            'author' => array(
                'name' => 'Me too',
            )
        );

        $writer->items[] = array(
            'title' => 'My article 3',
            'url' => 'http://foo/bar3'
        );

        $generated_output = $writer->execute();

        $expected_output = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <generator uri="https://github.com/fguillot/picoFeed">PicoFeed</generator>
  <title>My site</title>
  <id>http://boo/</id>
  <updated>'.date(DATE_ATOM).'</updated>
  <link rel="alternate" type="text/html" href="http://boo/"/>
  <link rel="self" type="application/atom+xml" href="http://boo/feed.atom"/>
  <author>
    <name>Me</name>
    <email>me@here</email>
    <uri>http://me</uri>
  </author>
  <entry>
    <title>My article 1</title>
    <id>http://foo/bar</id>
    <updated>'.date(DATE_ATOM, strtotime('-2 days')).'</updated>
    <link rel="alternate" type="text/html" href="http://foo/bar"/>
    <summary>Super summary</summary>
    <content type="html"><![CDATA[<p>content</p>]]></content>
  </entry>
  <entry>
    <title>My article 2</title>
    <id>http://foo/bar2</id>
    <updated>'.date(DATE_ATOM, strtotime('-1 day')).'</updated>
    <link rel="alternate" type="text/html" href="http://foo/bar2"/>
    <summary>Super summary 2</summary>
    <content type="html"><![CDATA[<p>content 2 &nbsp; &copy; 2015</p>]]></content>
    <author>
      <name>Me too</name>
    </author>
  </entry>
  <entry>
    <title>My article 3</title>
    <id>http://foo/bar3</id>
    <updated>'.date(DATE_ATOM).'</updated>
    <link rel="alternate" type="text/html" href="http://foo/bar3"/>
  </entry>
</feed>
';

        $this->assertEquals($expected_output, $generated_output);
    }
}