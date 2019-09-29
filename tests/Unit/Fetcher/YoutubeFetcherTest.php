<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Tests\Unit\Fetcher;

use \OCA\News\Db\Feed;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\YoutubeFetcher;

use PHPUnit\Framework\TestCase;

class YoutubeFetcherTest extends TestCase
{

    /**
     * Mocked fetcher.
     *
     * @var Fetcher
     */
    private $fetcher;

    /**
     * Mocked Feed Fetcher.
     *
     * @var FeedFetcher
     */
    private $feedFetcher;

    public function setUp()
    {
        $this->feedFetcher = $this->getMockBuilder(FeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher = new YoutubeFetcher($this->feedFetcher);
    }


    public function testCanHandleFails()
    {
        $url = 'http://youtube.com';
        $this->assertFalse($this->fetcher->canHandle($url));
    }


    public function testCanHandle()
    {
        $url = 'http://youtube.com/test/?test=a&list=b&b=c';
        $this->assertTrue($this->fetcher->canHandle($url));
    }


    public function testPlaylistUrl()
    {
        $url = 'http://youtube.com/something/weird?a=b&list=sobo3&c=1';
        $transformedUrl = 'http://gdata.youtube.com/feeds/api/playlists/sobo3';
        $favicon = true;
        $modified = 3;
        $fullTextEnabled = false;
        $user = 5;
        $password = 5;
        $feed = new Feed();
        $feed->setUrl('http://google.de');
        $result = [$feed, []];

        $this->feedFetcher->expects($this->once())
            ->method('fetch')
            ->with(
                $this->equalTo($transformedUrl),
                $this->equalTo($favicon),
                $this->equalTo($modified),
                $this->equalTo($fullTextEnabled),
                $this->equalTo($user)
            )
            ->will($this->returnValue($result));
        $feed = $this->fetcher->fetch($url, $favicon, $modified, $fullTextEnabled, $user, $password);

        $this->assertEquals($url, $result[0]->getUrl());
    }


}
