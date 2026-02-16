<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Service;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCA\News\Service\ExportService;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use PHPUnit\Framework\TestCase;

class ExportServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2 */
    private $feedService;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2 */
    private $itemService;

    /** @var ExportService */
    private $service;

    /** @var string */
    private $userId = 'testuser';

    protected function setUp(): void
    {
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new ExportService(
            $this->feedService,
            $this->itemService
        );
    }

    /**
     * Test that articles() returns an empty array when no items exist
     */
    public function testArticlesEmpty(): void
    {
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->userId)
            ->willReturn([]);

        $this->itemService->expects($this->exactly(2))
            ->method('findAllForUser')
            ->willReturn([]);

        $result = $this->service->articles($this->userId);

        $this->assertEmpty($result);
    }

    /**
     * Test that articles() correctly exports starred and unread items
     */
    public function testArticlesWithItems(): void
    {
        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn(1);

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->userId)
            ->willReturn([$feed]);

        $item = $this->createMock(Item::class);
        $item->method('getId')->willReturn(42);
        $item->method('toExport')
            ->willReturn(['id' => 42, 'title' => 'Test Article']);

        $this->itemService->expects($this->exactly(2))
            ->method('findAllForUser')
            ->willReturnOnConsecutiveCalls([$item], []);

        $result = $this->service->articles($this->userId);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Article', $result[0]['title']);
    }

    /**
     * Test that articles() deduplicates items that are both starred and unread
     */
    public function testArticlesDeduplicatesItems(): void
    {
        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn(1);

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->willReturn([$feed]);

        $item = $this->createMock(Item::class);
        $item->method('getId')->willReturn(42);
        $item->method('toExport')
            ->willReturn(['id' => 42, 'title' => 'Duped Article']);

        // Same item returned by both starred and unread queries
        $this->itemService->expects($this->exactly(2))
            ->method('findAllForUser')
            ->willReturnOnConsecutiveCalls([$item], [$item]);

        $result = $this->service->articles($this->userId);

        $this->assertCount(1, $result);
    }

    /**
     * Test that articles() handles multiple feeds correctly
     */
    public function testArticlesMultipleFeeds(): void
    {
        $feed1 = $this->createMock(Feed::class);
        $feed1->method('getId')->willReturn(1);
        $feed2 = $this->createMock(Feed::class);
        $feed2->method('getId')->willReturn(2);

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->willReturn([$feed1, $feed2]);

        $item1 = $this->createMock(Item::class);
        $item1->method('getId')->willReturn(10);
        $item1->method('toExport')
            ->willReturn(['id' => 10, 'title' => 'From Feed 1']);

        $item2 = $this->createMock(Item::class);
        $item2->method('getId')->willReturn(20);
        $item2->method('toExport')
            ->willReturn(['id' => 20, 'title' => 'From Feed 2']);

        $this->itemService->expects($this->exactly(2))
            ->method('findAllForUser')
            ->willReturnOnConsecutiveCalls([$item1], [$item2]);

        $result = $this->service->articles($this->userId);

        $this->assertCount(2, $result);
    }
}
