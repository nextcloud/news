<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;


class Rss10ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PicoFeed\Parser\MalformedXmlException
     */
    public function testBadInput()
    {
        $parser = new Rss10('boo');
        $parser->execute();
    }

    public function testGetItemsTree()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertCount(2, $feed->items);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertCount(3, $feed->items);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertCount(1, $feed->items);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals(array(), $feed->items);
    }

    public function testFindFeedTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_feed_values.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindFeedDescription()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getDescription());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getDescription());
    }

    public function testFindFeedLogo()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getLogo());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getLogo());
    }

    public function testFindFeedIcon()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getIcon());
    }

    public function testFindFeedUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getFeedUrl());
    }

    public function testFindSiteUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_extra.xml'), '', 'https://feeds.wikipedia.org/category/Russian-language_literature.xml'); // relative url
        $feed = $parser->execute();
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getSiteUrl());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getSiteUrl());
    }

    public function testFindFeedId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getId());
    }

    public function testFindFeedDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
    }

    public function testFindFeedLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('ru', $feed->getLanguage());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('ru', $feed->getLanguage());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('ru', $feed->getLanguage());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindItemId()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', $feed->items[0]->getId());
    }

    public function testFindItemUrl()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <feedburner:origLink>

        // relative urls
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_extra.xml'), '', 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $feed = $parser->execute();
        $this->assertEquals('https://feeds.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <feedburner:origLink>

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_element_preference.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <feedburner:origLink> is preferred over <rss:link>

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getUrl());
    }

    public function testFindItemTitle()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_item_values.xml'));
        $feed = $parser->execute();
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getTitle());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getTitle());
    }

    /*
     * TODO: Add test of feed date fallback
     */
    public function testFindItemDate()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp()); // item date
        $this->assertEquals(1433451900, $feed->items[1]->getDate()->getTimestamp()); // fallback to feed date

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals(time(), $feed->items[0]->getDate()->getTimestamp(), 1);
    }

    public function testFindItemLanguage()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('bg', $feed->items[0]->getLanguage()); // item language
        $this->assertEquals('ru', $feed->items[1]->getLanguage()); // fallback to feed language

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testFindItemAuthor()
    {
        // items[0] === item author
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testFindItemContent()
    {
        // items[0] === <description>
        // items[1] === <content:encoded>
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);

        // <content:encoding> is preferred over <description>
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_element_preference.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_item_values.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertTrue(strpos($feed->items[1]->getContent(), "Осенью 1865 года, потеряв  все свои\nденьги в казино") === 0); // <content:encoded> => <description>

        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $parser->disableContentFiltering();
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getContent());
    }

    public function testFindItemEnclosure()
    {
        $parser = new Rss10(file_get_contents('tests/fixtures/rss_10.xml'));
        $feed = $parser->execute();
        $this->assertEquals('', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('', $feed->items[0]->getEnclosureType());
    }
}