<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Db;


class ItemTest extends \PHPUnit_Framework_TestCase {

    private $item;

    protected function setUp(){
        $this->item = new Item();
        $this->item->setStatus(0);
    }


    public function testSetRead(){
        $this->item->setRead();

        $this->assertTrue($this->item->isRead());
    }


    public function testSetUnread(){
        $this->item->setUnread();

        $this->assertTrue($this->item->isUnread());
    }


    public function testSetStarred(){
        $this->item->setStarred();

        $this->assertTrue($this->item->isStarred());
    }


    public function testSetUnstarred(){
        $this->item->setUnstarred();

        $this->assertTrue($this->item->isUnstarred());
    }


    public function testToAPI() {
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
        $item->setRtl(true);
        $item->setFeedId(1);
        $item->setStatus(0);
        $item->setUnread();
        $item->setStarred();
        $item->setLastModified(321);
        $item->setFingerprint('fingerprint');

        $this->assertEquals([
            'id' => 3,
            'guid' => 'guid',
            'guidHash' => 'hash',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'body' => 'body',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'feedId' => 1,
            'unread' => true,
            'starred' => true,
            'lastModified' => 321,
            'rtl' => true,
            'fingerprint' => 'fingerprint'
            ], $item->toAPI());
    }


    public function testJSONSerialize() {
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
        $item->setFeedId(1);
        $item->setStatus(0);
        $item->setRtl(true);
        $item->setUnread();
        $item->setFingerprint('fingerprint');
        $item->setStarred();
        $item->setLastModified(321);

        $this->assertEquals([
            'id' => 3,
            'guid' => 'guid',
            'guidHash' => 'hash',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'body' => '<body><div>this is a test</body>',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'feedId' => 1,
            'unread' => true,
            'starred' => true,
            'lastModified' => 321,
            'rtl' => true,
            'intro' => 'this is a test',
            'fingerprint' => 'fingerprint'
            ], $item->jsonSerialize());
    }

    public function testToExport() {
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
        $item->setFeedId(1);
        $item->setRtl(true);
        $item->setStatus(0);
        $item->setRead();
        $item->setStarred();
        $item->setLastModified(321);

        $feed = new Feed();
        $feed->setLink('http://test');
        $feeds = ["feed1" => $feed];

        $this->assertEquals([
            'guid' => 'guid',
            'url' => 'https://google',
            'title' => 'title',
            'author' => 'author',
            'pubDate' => 123,
            'body' => 'body',
            'enclosureMime' => 'audio/ogg',
            'enclosureLink' => 'enclink',
            'unread' => false,
            'starred' => true,
            'feedLink' => 'http://test',
            'rtl' => true
            ], $item->toExport($feeds));
    }


    private function createImportItem($isRead) {
        $item = new Item();
        $item->setGuid('guid');
        $item->setGuidHash('guid');
        $item->setUrl('https://google');
        $item->setTitle('title');
        $item->setAuthor('author');
        $item->setPubDate(123);
        $item->setBody('body');
        $item->setEnclosureMime('audio/ogg');
        $item->setEnclosureLink('enclink');
        $item->setStarred();
        $item->setRtl(true);

        if ($isRead) {
            $item->setUnread();
        } else {
            $item->setRead();
        }

        return $item;
    }


    public function testSearchIndex() {
        $item = new Item();
        $item->setBody('<a>somEth&auml;ng</a>');
        $item->setUrl('http://link');
        $item->setAuthor('&auml;uthor');
        $item->setTitle('<a>t&auml;tle</a>');
        $item->generateSearchIndex();
        $expected = 'somethängäuthortätlehttp://link';
        $this->assertEquals($expected, $item->getSearchIndex());
    }


    public function testFromImport() {
        $item = $this->createImportItem(false);

        $import = [
            'guid' => $item->getGuid(),
            'url' => $item->getUrl(),
            'title' => $item->getTitle(),
            'author' => $item->getAuthor(),
            'pubDate' => $item->getPubDate(),
            'body' => $item->getBody(),
            'enclosureMime' => $item->getEnclosureMime(),
            'enclosureLink' => $item->getEnclosureLink(),
            'unread' => $item->isUnread(),
            'starred' => $item->isStarred(),
            'rtl' => $item->getRtl()
        ];

        $compareWith = Item::fromImport($import);

        $this->assertEquals($item, $compareWith);
    }


    public function testFromImportRead() {
        $item = $this->createImportItem(true);

        $import = [
            'guid' => $item->getGuid(),
            'url' => $item->getUrl(),
            'title' => $item->getTitle(),
            'author' => $item->getAuthor(),
            'pubDate' => $item->getPubDate(),
            'body' => $item->getBody(),
            'enclosureMime' => $item->getEnclosureMime(),
            'enclosureLink' => $item->getEnclosureLink(),
            'unread' => $item->isUnread(),
            'starred' => $item->isStarred(),
            'rtl' => $item->getRtl()
        ];

        $compareWith = Item::fromImport($import);

        $this->assertEquals($item, $compareWith);
    }



    public function testSetAuthor(){
        $item = new Item();
        $item->setAuthor('<a>my link</li>');
        $this->assertEquals('my link', $item->getAuthor());
        $this->assertContains('author', $item->getUpdatedFields());
    }


    public function testSetTitle(){
        $item = new Item();
        $item->setTitle('<a>my link</li>');
        $this->assertEquals('my link', $item->getTitle());
        $this->assertContains('title', $item->getUpdatedFields());
    }


    public function testSetXSSUrl() {
        $item = new Item();
        $item->setUrl('javascript:alert()');
        $this->assertEquals('', $item->getUrl());
    }


    public function testSetMagnetUrl() {
        $item = new Item();
        $item->setUrl('magnet://link.com');
        $this->assertEquals('magnet://link.com', $item->getUrl());
    }


    public function testMakeLinksInBodyOpenNewTab() {
        $item = new Item();
        $item->setBody("<a href=\"test\">ha</a>");
        $this->assertEquals("<a target=\"_blank\" rel=\"noreferrer\" href=\"test\">ha</a>",
            $item->getBody());
    }

    public function testComputeFingerPrint() {
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

        $this->assertEquals(md5($title . $url . $body . $link),
                            $item->getFingerprint());
    }

}
