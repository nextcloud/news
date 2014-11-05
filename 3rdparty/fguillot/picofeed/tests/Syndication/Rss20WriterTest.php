<?php
namespace PicoFeed\Syndication;

use PHPUnit_Framework_TestCase;


class Rss20WriterTest extends PHPUnit_Framework_TestCase
{
    public function testWriter()
    {
        $writer = new Rss20();
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
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <generator>PicoFeed (https://github.com/fguillot/picoFeed)</generator>
    <title>My site</title>
    <description>My site</description>
    <pubDate>'.date(DATE_RFC822).'</pubDate>
    <atom:link href="http://boo/feed.atom" rel="self" type="application/rss+xml"/>
    <link>http://boo/</link>
    <webMaster>me@here (Me)</webMaster>
    <item>
      <title>My article 1</title>
      <link>http://foo/bar</link>
      <guid isPermaLink="true">http://foo/bar</guid>
      <pubDate>'.date(DATE_RFC822, strtotime('-2 days')).'</pubDate>
      <description>Super summary</description>
      <content:encoded><![CDATA[<p>content</p>]]></content:encoded>
    </item>
    <item>
      <title>My article 2</title>
      <link>http://foo/bar2</link>
      <guid isPermaLink="true">http://foo/bar2</guid>
      <pubDate>'.date(DATE_RFC822, strtotime('-1 day')).'</pubDate>
      <description>Super summary 2</description>
      <content:encoded><![CDATA[<p>content 2 &nbsp; &copy; 2015</p>]]></content:encoded>
    </item>
    <item>
      <title>My article 3</title>
      <link>http://foo/bar3</link>
      <guid isPermaLink="true">http://foo/bar3</guid>
      <pubDate>'.date(DATE_RFC822).'</pubDate>
    </item>
  </channel>
</rss>
';

        $this->assertEquals($expected_output, $generated_output);
    }
}