<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Tests\Unit\Db;


use OCA\News\Db\Feed;

class FeedTest extends \PHPUnit_Framework_TestCase {


    private function createFeed() {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setHttpLastModified(44);
        $feed->setHttpEtag(45);
        $feed->setUrl('http://google.com/some/weird/path');
        $feed->setTitle('title');
        $feed->setFaviconLink('favicon');
        $feed->setAdded(123);
        $feed->setFolderId(1);
        $feed->setUnreadCount(321);
        $feed->setLink('https://www.google.com/some/weird/path');
        $feed->setLocation('http://google.at');
        $feed->setOrdering(2);
        $feed->setFullTextEnabled(true);
        $feed->setPinned(true);
        $feed->setUpdateMode(1);
        $feed->setUpdateErrorCount(2);
        $feed->setLastUpdateError('hi');
        $feed->setBasicAuthUser('user');
        $feed->setBasicAuthPassword('password');
        return $feed;
    }

    public function testToAPI() {
        $feed = $this->createFeed();

        $this->assertEquals([
            'id' => 3,
            'url' => 'http://google.com/some/weird/path',
            'title' => 'title',
            'faviconLink' => 'favicon',
            'added' => 123,
            'folderId' => 1,
            'unreadCount' => 321,
            'ordering' => 2,
            'pinned' => true,
            'link' => 'https://www.google.com/some/weird/path',
            'updateErrorCount' => 2,
            'lastUpdateError' => 'hi'
        ], $feed->toAPI());
    }


    public function testSerialize() {
        $feed = $this->createFeed();

        $this->assertEquals([
            'id' => 3,
            'url' => 'http://google.com/some/weird/path',
            'title' => 'title',
            'faviconLink' => 'favicon',
            'added' => 123,
            'folderId' => 1,
            'unreadCount' => 321,
            'link' => 'https://www.google.com/some/weird/path',
            'userId' => null,
            'urlHash' => '44168618f55392b145629d6b3922e84b',
            'preventUpdate' => null,
            'deletedAt' => null,
            'articlesPerUpdate' => null,
            'cssClass' => 'custom-google-com',
            'location' => 'http://google.at',
            'ordering' => 2,
            'fullTextEnabled' => true,
            'pinned' => true,
            'updateMode' => 1,
            'updateErrorCount' => 2,
            'lastUpdateError' => 'hi',
            'basicAuthUser' => 'user',
            'basicAuthPassword' => 'password'
        ], $feed->jsonSerialize());
    }


    public function testSetXSSUrl() {
        $feed = new Feed();
        $feed->setUrl('javascript:alert()');
        $this->assertEquals('', $feed->getUrl());
    }


    public function testSetUrlUpdatesHash() {
        $feed = new Feed();
        $feed->setUrl('http://test');
        $this->assertEquals(md5('http://test'), $feed->getUrlHash());
    }


    public function testSetXSSLink() {
        $feed = new Feed();
        $feed->setLink('javascript:alert()');
        $this->assertEquals('', $feed->getLink());
    }


}
