<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\Db;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;

use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{

    /**
     * @var Item
     */
    private $item;

    protected function setUp(): void
    {
        $this->item = new Item();
    }


    public function testSetRead()
    {
        $this->item->setUnread(false);

        $this->assertFalse($this->item->isUnread());
    }


    public function testSetUnread()
    {
        $this->item->setUnread(true);

        $this->assertTrue($this->item->isUnread());
    }


    public function testSetStarred()
    {
        $this->item->setStarred(true);

        $this->assertTrue($this->item->isStarred());
    }


    public function testSetUnstarred()
    {
        $this->item->setStarred(false);

        $this->assertFalse($this->item->isStarred());
    }


    public function testToAPI()
    {
        $item = new Item();
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('hash');
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('body');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setMediaThumbnail('https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg');
        $item->setMediaDescription('The best video ever');
        $item->setRtl(true);
        $item->setFeedId(1);
        $item->setUnread(true);
        $item->setStarred(true);
        $item->setLastModified('1111111111234567');
        $item->setFingerprint('fingerprint');
        $item->setContentHash('contentHash');

        $this->assertEquals(
            [
            'id' => 3,
            'guid' => 'guid',
            'guidHash' => 'hash',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'updatedDate' => null,
            'body' => 'body',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'mediaThumbnail' => 'https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg',
            'mediaDescription' => 'The best video ever',
            'feedId' => 1,
            'unread' => true,
            'starred' => true,
            'lastModified' => 1111111111,
            'rtl' => true,
            'fingerprint' => 'fingerprint',
            'contentHash' => 'contentHash'
            ],
            $item->toAPI()
        );
    }


    public function testToAPI2()
    {
        $item = new Item();
        $item->setId(3);
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('body');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setMediaThumbnail('https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg');
        $item->setMediaDescription('The best video ever');
        $item->setRtl(true);
        $item->setFeedId(1);
        $item->setUnread(true);
        $item->setStarred(true);
        $item->setLastModified('1111111111234567');
        $item->setFingerprint('fingerprint');
        $item->setContentHash('contentHash');

        $this->assertEquals(
            [
                'id' => 3,
                'url' => 'https://google',
                'title' => 'title',
                'author' => 'author',
                'publishedAt' => date('c', 123),
                'lastModifiedAt' => date('c', 1111111111),
                'enclosure' => [
                    'mimeType' => 'audio/ogg',
                    'url' => 'enclink',
                ],
                'body' => 'body',
                'feedId' => 1,
                'isUnread' => true,
                'isStarred' => true,
                'fingerprint' => 'fingerprint',
                'contentHash' => 'contentHash'
            ],
            $item->toAPI2()
        );
    }


    public function testToAPI2Reduced()
    {
        $item = new Item();
        $item->setId(3);
        $item->setUnread(true);
        $item->setStarred(true);

        $this->assertEquals(
            [
                'id' => 3,
                'isUnread' => true,
                'isStarred' => true
            ],
            $item->toAPI2(true)
        );
    }


    public function testJSONSerialize()
    {
        $item = new Item();
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('hash');
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('<body><div>this is a test</body>');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setMediaThumbnail('https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg');
        $item->setMediaDescription('The best video ever');
        $item->setFeedId(1);
        $item->setRtl(true);
        $item->setUnread(true);
        $item->setFingerprint('fingerprint');
        $item->setStarred(true);
        $item->setLastModified(321);
        $item->setCategories(['food']);
        $item->setSharedBy('jack');
        $item->setSharedByDisplayName('Jack');

        $this->assertEquals(
            [
            'id' => 3,
            'guid' => 'guid',
            'guidHash' => 'hash',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'updatedDate' => null,
            'body' => '<body><div>this is a test</body>',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'mediaThumbnail' => 'https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg',
            'mediaDescription' => 'The best video ever',
            'feedId' => 1,
            'unread' => true,
            'starred' => true,
            'lastModified' => 321,
            'rtl' => true,
            'intro' => 'this is a test',
            'fingerprint' => 'fingerprint',
            'categories' => ['food'],
            'sharedBy' => 'jack',
            'sharedByDisplayName' => 'Jack'
            ],
            $item->jsonSerialize()
        );
    }

    public function testToExport()
    {
        $item = new Item();
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('hash');
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('body');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setMediaThumbnail('https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg');
        $item->setMediaDescription('The best video ever');
        $item->setFeedId(1);
        $item->setRtl(true);
        $item->setUnread(false);
        $item->setStarred(true);
        $item->setLastModified(321);

        $feed = new Feed();
        $feed->setLink('http://test');
        $feeds = ["feed1" => $feed];

        $this->assertEquals(
            [
            'guid' => 'guid',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'updatedDate' => null,
            'body' => 'body',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'mediaThumbnail' => 'https://i2.ytimg.com/vi/E6B3uvhrcQk/hqdefault.jpg',
            'mediaDescription' => 'The best video ever',
            'unread' => false,
            'starred' => true,
            'feedLink' => 'http://test',
            'rtl' => true
            ],
            $item->toExport($feeds)
        );
    }


    private function createImportItem($isRead)
    {
        $item = new Item();
        $item->setGuid('guid');
        $item->setGuidHash('1e0ca5b1252f1f6b1e0ac91be7e7219e');
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('body');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setStarred(true);
        $item->setRtl(true);

        if ($isRead) {
            $item->setUnread(true);
        } else {
            $item->setUnread(false);
        }

        return $item;
    }


    public function testSearchIndex()
    {
        $item = new Item();
        $item->setBody('<a>somEth&auml;ng</a>');
        $item->setUrl('http://link');
        $item->setAuthor('&auml;uthor');
        $item->setTitle('<a>t&auml;tle</a>');
        $item->setCategories(['food', 'travel']);
        $item->generateSearchIndex();
        $expected = 'somethängäuthortätlefoodtravelhttp://link';
        $this->assertEquals($expected, $item->getSearchIndex());
    }

    public function testSearchIndexNull()
    {
        $item = new Item();
        $item->setBody('<a>somEth&auml;ng</a>');
        $item->setUrl('http://link');
        $item->setAuthor(null);
        $item->setTitle('<a>t&auml;tle</a>');
        $item->setCategories(['food', 'travel']);
        $item->generateSearchIndex();
        $expected = 'somethängtätlefoodtravelhttp://link';
        $this->assertEquals($expected, $item->getSearchIndex());
    }

    public function testFromImport()
    {
        $item = $this->createImportItem(false);

        $import = [
            'guid' => $item->getGuid(),
            'url' => $item->getUrl(),
            'title' => $item->getTitle(),
            'author' => $item->getAuthor(),
            'pubDate' => $item->getPubDate(),
            'updatedDate' => null,
            'body' => $item->getBody(),
            'enclosureMime' => $item->getEnclosureMime(),
            'enclosureLink' => $item->getEnclosureLink(),
            'mediaThumbnail' => $item->getMediaThumbnail(),
            'mediaDescription' => $item->getMediaDescription(),
            'unread' => $item->isUnread(),
            'starred' => $item->isStarred(),
            'rtl' => $item->getRtl()
        ];

        $compareWith = Item::fromImport($import);

        $this->assertEquals($item, $compareWith);
    }


    public function testFromImportRead()
    {
        $item = $this->createImportItem(true);

        $import = [
            'guid' => $item->getGuid(),
            'url' => $item->getUrl(),
            'title' => $item->getTitle(),
            'author' => $item->getAuthor(),
            'pubDate' => $item->getPubDate(),
            'updatedDate' => null,
            'body' => $item->getBody(),
            'enclosureMime' => $item->getEnclosureMime(),
            'enclosureLink' => $item->getEnclosureLink(),
            'mediaThumbnail' => $item->getMediaThumbnail(),
            'mediaDescription' => $item->getMediaDescription(),
            'unread' => $item->isUnread(),
            'starred' => $item->isStarred(),
            'rtl' => $item->getRtl()
        ];

        $compareWith = Item::fromImport($import);

        $this->assertEquals($item, $compareWith);
    }



    public function testSetAuthor()
    {
        $item = new Item();
        $item->setAuthor('<a>my link</li>');
        $this->assertEquals('my link', $item->getAuthor());
        $this->assertArrayHasKey('author', $item->getUpdatedFields());
    }


    public function testSetTitle()
    {
        $item = new Item();
        $item->setTitle('<a>my link</li>');
        $this->assertEquals('my link', $item->getTitle());
        $this->assertArrayHasKey('title', $item->getUpdatedFields());
    }


    public function testSetXSSUrl()
    {
        $item = new Item();
        $item->setUrl('javascript:alert()');
        $this->assertEquals('', $item->getUrl());
    }


    public function testSetMagnetUrl()
    {
        $item = new Item();
        $item->setUrl('magnet://link.com');
        $this->assertEquals('magnet://link.com', $item->getUrl());
    }


    public function testMakeLinksInBodyOpenNewTab()
    {
        $item = new Item();
        $item->setBody("<a href=\"test\">ha</a>");
        $this->assertEquals(
            "<a target=\"_blank\" rel=\"noreferrer\" href=\"test\">ha</a>",
            $item->getBody()
        );
    }

    public function testComputeFingerPrint()
    {
        $title = 'a';
        $body = 'b';
        $url = 'http://google.com';
        $link = 'ho';

        $item = new Item();
        $item->setBody($body);
        $item->setTitle($title);
        $item->setUrl($url);
        $item->setEnclosureLink($link);
        $item->generateSearchIndex();

        $this->assertEquals(
            md5($title . $url . $body . $link),
            $item->getFingerprint()
        );
    }

    public function testSetCategories()
    {
        $item = new Item();
        $item->setCategories(['podcast', 'blog']);
        $this->assertEquals(['podcast', 'blog'], $item->getCategories());
        $this->assertArrayHasKey('categoriesJson', $item->getUpdatedFields());
    }

    public function testSetCategoriesJson()
    {
        $item = new Item();
        $item->setCategoriesJson(json_encode(['podcast', 'blog']));
        $this->assertEquals(json_encode(['podcast', 'blog']), $item->getCategoriesJson());
        $this->assertArrayHasKey('categoriesJson', $item->getUpdatedFields());
    }

    public function testSetSharedBy()
    {
        $item = new Item();
        $item->setSharedBy('Hector');
        $this->assertEquals('Hector', $item->getSharedBy());
        $this->assertArrayHasKey('sharedBy', $item->getUpdatedFields());
    }

    public function testSetSharedByDisplayName()
    {
        $item = new Item();
        $item->setSharedByDisplayName('Hector');
        $this->assertEquals('Hector', $item->getSharedByDisplayName());
    }
}
