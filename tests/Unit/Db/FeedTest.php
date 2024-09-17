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

use PHPUnit\Framework\TestCase;
use OCA\News\Db\Feed;

class FeedTest extends TestCase
{


    private function createFeed()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setHttpLastModified(44);
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

    public function testToAPI()
    {
        $feed = $this->createFeed();

        $this->assertEquals(
            [
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
                'lastUpdateError' => 'hi',
                'items' => [],
            ],
            $feed->toAPI()
        );
    }


    public function testToAPI2()
    {
        $feed = $this->createFeed();

        $this->assertEquals(
            [
                'id' => 3,
                'name' => 'title',
                'faviconLink' => 'favicon',
                'folderId' => 1,
                'ordering' => 2,
                'fullTextEnabled' => true,
                'updateMode' => 1,
                'isPinned' => true,
                'error' => [
                    'code' => 1,
                    'message' => 'hi'
                ]
            ],
            $feed->toAPI2()
        );
    }


    public function testSerialize()
    {
        $feed = $this->createFeed();

        $this->assertEquals(
            [
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
            ],
            $feed->jsonSerialize()
        );
    }


    public function testSetXSSUrl()
    {
        $this->expectException(\TypeError::class);

        $feed = new Feed();
        $feed->setUrl('javascript:alert()');
        $feed->getUrl();
    }


    public function testSetUrlUpdatesHash()
    {
        $feed = new Feed();
        $feed->setUrl('http://test');
        $this->assertEquals(md5('http://test'), $feed->getUrlHash());
    }


    public function testSetXSSLink()
    {
        $feed = new Feed();
        $feed->setLink('javascript:alert()');
        $this->assertEquals('', $feed->getLink());
    }


    public function testSetAdded()
    {
        $feed = new Feed();
        $feed->setAdded(15);
        $this->assertEquals(15, $feed->getAdded());
    }
    public function testSetDeletedAt()
    {
        $feed = new Feed();
        $feed->setDeletedAt(15);
        $this->assertEquals(15, $feed->getDeletedAt());
    }
    public function testSetFaviconLink()
    {
        $feed = new Feed();
        $feed->setFaviconLink('https://url');
        $this->assertEquals('https://url', $feed->getFaviconLink());
    }
    public function testSetLastModified()
    {
        $feed = new Feed();
        $feed->setLastModified('15');
        $this->assertEquals('15', $feed->getLastModified());
    }
    public function testSetLastUpdateError()
    {
        $feed = new Feed();
        $feed->setLastUpdateError('NO');
        $this->assertEquals('NO', $feed->getLastUpdateError());
    }
    public function testSetUpdateErrorCount()
    {
        $feed = new Feed();
        $feed->setUpdateErrorCount('5');
        $this->assertEquals('5', $feed->getUpdateErrorCount());
    }
    public function testSetOrdering()
    {
        $feed = new Feed();
        $feed->setOrdering(1);
        $this->assertEquals(1, $feed->getOrdering());
    }
    public function testSetPinned()
    {
        $feed = new Feed();
        $feed->setPinned(true);
        $this->assertEquals(true, $feed->getPinned());
    }
}
