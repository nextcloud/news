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


namespace OCA\News\Tests\Unit\Service;

use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ImportService;
use OCA\News\Service\ItemServiceV2;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImportServiceTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;

    /** @var ImportService */
    private $class;

    /**
     * @var string
     */
    private $uid;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\HTMLPurifier
     */
    private $purifier;

    private $time;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this
            ->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purifier = $this
            ->getMockBuilder(\HTMLPurifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 333333;

        $this->class = new ImportService(
            $this->feedService,
            $this->itemService,
            $this->purifier,
            $this->logger
        );
        $this->uid = 'jack';
    }


    public function testImportArticles()
    {
        $url = 'http://nextcloud/nofeed';

        $feed = new Feed();
        $feed->setId(3);
        $feed->setUserId($this->uid);
        $feed->setUrl($url);
        $feed->setLink($url);
        $feed->setTitle('Articles without feed');
        $feed->setAdded($this->time);
        $feed->setFolderId(0);
        $feed->setPreventUpdate(true);

        $feeds = [$feed];

        $item = new Item();
        $item->setFeedId(3);
        $item->setAuthor('john');
        $item->setGuid('s');
        $item->setGuidHash('03c7c0ace395d80182db07ae2c30f034');
        $item->setTitle('hey');
        $item->setPubDate(333);
        $item->setBody('come over');
        $item->setEnclosureMime('mime');
        $item->setEnclosureLink('lin');
        $item->setUnread(true);
        $item->setStarred(false);
        $item->generateSearchIndex();

        $json = $item->toExport(['feed3' => $feed]);

        $items = [$json];

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('insertOrUpdate')
            ->with($item);

        $this->purifier->expects($this->once())
            ->method('purify')
            ->with($this->equalTo($item->getBody()))
            ->will($this->returnValue($item->getBody()));

        $result = $this->class->importArticles($this->uid, $items);

        $this->assertEquals(null, $result);
    }


    public function testImportArticlesCreatesOwnFeedWhenNotFound()
    {
        $url = 'http://nextcloud/args';

        $feed = new Feed();
        $feed->setId(3);
        $feed->setUserId($this->uid);
        $feed->setUrl($url);
        $feed->setLink($url);
        $feed->setTitle('Articles without feed');
        $feed->setAdded($this->time);
        $feed->setFolderId(0);
        $feed->setPreventUpdate(true);

        $feeds = [$feed];

        $item = new Item();
        $item->setFeedId(3);
        $item->setAuthor('john');
        $item->setGuid('s');
        $item->setGuidHash('03c7c0ace395d80182db07ae2c30f034');
        $item->setTitle('hey');
        $item->setPubDate(333);
        $item->setBody('come over');
        $item->setEnclosureMime('mime');
        $item->setEnclosureLink('lin');
        $item->setUnread(true);
        $item->setStarred(false);
        $item->generateSearchIndex();

        $json = $item->toExport(['feed3' => $feed]);
        $json2 = $json;
        // believe it or not this copies stuff :D
        $json2['feedLink'] = 'http://test.com';

        $items = [$json, $json2];

        $insertFeed = new Feed();
        $insertFeed->setLink('http://nextcloud/nofeed');
        $insertFeed->setUrl('http://nextcloud/nofeed');
        $insertFeed->setUserId($this->uid);
        $insertFeed->setTitle('Articles without feed');
        $insertFeed->setAdded($this->time);
        $insertFeed->setPreventUpdate(true);
        $insertFeed->setFolderId(null);

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($feeds));
        $this->feedService->expects($this->once())
            ->method('insert')
            ->will(
                $this->returnCallback(
                    function () use ($insertFeed) {
                        $insertFeed->setId(3);
                        return $insertFeed;
                    }
                )
            );

        $this->itemService->expects($this->exactly(2))
            ->method('insertOrUpdate')
            ->withConsecutive([$item]);
        $this->purifier->expects($this->exactly(2))
            ->method('purify')
            ->with($this->equalTo($item->getBody()))
            ->will($this->returnValue($item->getBody()));

        $this->feedService->expects($this->once())
            ->method('findByUrl')
            ->will($this->returnValue($feed));

        $result = $this->class->importArticles($this->uid, $items);

        $this->assertEquals($feed, $result);
    }
}
