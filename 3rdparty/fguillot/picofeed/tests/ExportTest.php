<?php

require_once 'lib/PicoFeed/PicoFeed.php';

use PicoFeed\Export;

class ExportTest extends PHPUnit_Framework_TestCase
{
    public function testOuput()
    {
        $feeds = array(
            array(
                'title' => 'Site title',
                'description' => 'Optional description',
                'site_url' => 'http://blabla.fr/',
            ),
            array(
                'title' => 'Site title',
                'description' => 'Optional description',
                'site_url' => 'http://petitcodeur.fr/',
                'feed_url' => 'http://petitcodeur.fr/feed.xml',
            )
        );

        $export = new Export($feeds);
        $opml = $export->execute();

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<opml><head><title>OPML Export</title></head><body><outline xmlUrl="http://petitcodeur.fr/feed.xml" htmlUrl="http://petitcodeur.fr/" title="Site title" text="Site title" description="Optional description" type="rss" version="RSS"/></body></opml>
';

        $this->assertEquals($expected, $opml);
    }

    public function testCategoryOuput()
    {
        $feeds = array(
            'my category' => array(
                array(
                    'title' => 'Site title',
                    'description' => 'Optional description',
                    'site_url' => 'http://blabla.fr/',
                ),
                array(
                    'title' => 'Site title',
                    'description' => 'Optional description',
                    'site_url' => 'http://petitcodeur.fr/',
                    'feed_url' => 'http://petitcodeur.fr/feed.xml',
                )
            ),
            'another category' => array(
                array(
                    'title' => 'Site title',
                    'description' => 'Optional description',
                    'site_url' => 'http://youpi.ici/',
                    'feed_url' => 'http://youpi.ici/feed.xml',
                )
            )
        );

        $export = new Export($feeds);
        $opml = $export->execute();

        $expected = '<?xml version="1.0" encoding="utf-8"?>
<opml><head><title>OPML Export</title></head><body><outline text="my category"><outline xmlUrl="http://petitcodeur.fr/feed.xml" htmlUrl="http://petitcodeur.fr/" title="Site title" text="Site title" description="Optional description" type="rss" version="RSS"/></outline><outline text="another category"><outline xmlUrl="http://youpi.ici/feed.xml" htmlUrl="http://youpi.ici/" title="Site title" text="Site title" description="Optional description" type="rss" version="RSS"/></outline></body></opml>
';

        $this->assertEquals($expected, $opml);
    }
}