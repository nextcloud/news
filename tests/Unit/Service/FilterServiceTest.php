<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Service;

use OCA\News\Db\Filter;
use OCA\News\Db\FilterMapperV2;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\FilterService;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FilterServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|FilterMapperV2 */
    private $mapper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ItemMapperV2 */
    private $itemMapper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface */
    private $logger;

    /** @var FilterService */
    private $service;

    protected function setUp(): void
    {
        $this->mapper = $this->getMockBuilder(FilterMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMapper = $this->getMockBuilder(ItemMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new FilterService($this->mapper, $this->itemMapper, $this->logger);
    }

    public function testApplyFiltersQueriesUnreadOnly(): void
    {
        $filter = new Filter();
        $filter->setFeedId(5);
        $filter->setTitleKeywords('foo');

        $itemMatch = Item::fromParams([
            'id' => 1,
            'title' => 'foo article',
            'unread' => true,
            'filtered' => false,
        ]);
        $itemNoMatch = Item::fromParams([
            'id' => 2,
            'title' => 'bar article',
            'unread' => true,
            'filtered' => false,
        ]);

        $this->mapper->expects($this->once())
            ->method('findByFeedId')
            ->with('jack', 5)
            ->willReturn($filter);
        $this->itemMapper->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('jack', 5, 0, true)
            ->willReturn([$itemMatch, $itemNoMatch]);
        $this->itemMapper->expects($this->once())
            ->method('update')
            ->with($itemMatch)
            ->willReturn($itemMatch);

        $marked = $this->service->applyFilters('jack', 5);

        $this->assertEquals(1, $marked);
        $this->assertFalse($itemMatch->isUnread());
        $this->assertTrue($itemMatch->isFiltered());
        $this->assertTrue($itemNoMatch->isUnread());
        $this->assertFalse($itemNoMatch->isFiltered());
    }

    public function testClearAndReapplyKeepsReadFilteredIfStillMatching(): void
    {
        $filter = new Filter();
        $filter->setFeedId(7);
        $filter->setTitleKeywords('keep');

        $readMatch = Item::fromParams([
            'id' => 1,
            'title' => 'keep this',
            'unread' => false,
            'filtered' => false,
        ]);
        $readNoMatch = Item::fromParams([
            'id' => 2,
            'title' => 'drop this',
            'unread' => false,
            'filtered' => true,
        ]);
        $unreadMatch = Item::fromParams([
            'id' => 3,
            'title' => 'keep unread',
            'unread' => true,
            'filtered' => false,
        ]);
        $unreadNoMatch = Item::fromParams([
            'id' => 4,
            'title' => 'another',
            'unread' => true,
            'filtered' => true,
        ]);

        $this->mapper->expects($this->once())
            ->method('findByFeedId')
            ->with('jack', 7)
            ->willReturn($filter);
        $this->itemMapper->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('jack', 7, 0, false)
            ->willReturn([$readMatch, $readNoMatch, $unreadMatch, $unreadNoMatch]);
        $this->itemMapper->expects($this->exactly(4))
            ->method('update')
            ->withAnyParameters();

        $marked = $this->service->clearAndReapplyFilter('jack', 7);

        $this->assertEquals(1, $marked);
        $this->assertTrue($readMatch->isFiltered());
        $this->assertFalse($readNoMatch->isFiltered());
        $this->assertFalse($unreadMatch->isUnread());
        $this->assertTrue($unreadMatch->isFiltered());
        $this->assertTrue($unreadNoMatch->isUnread());
        $this->assertFalse($unreadNoMatch->isFiltered());
    }

    public function testClearAndReapplyWithoutFilterClearsFilteredFlags(): void
    {
        $readFiltered = Item::fromParams([
            'id' => 11,
            'title' => 'old',
            'unread' => false,
            'filtered' => true,
        ]);
        $unreadFiltered = Item::fromParams([
            'id' => 12,
            'title' => 'old unread',
            'unread' => true,
            'filtered' => true,
        ]);

        $this->mapper->expects($this->once())
            ->method('findByFeedId')
            ->with('jack', 9)
            ->will($this->throwException(new DoesNotExistException('none')));
        $this->itemMapper->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('jack', 9, 0, false)
            ->willReturn([$readFiltered, $unreadFiltered]);
        $this->itemMapper->expects($this->exactly(2))
            ->method('update')
            ->withAnyParameters();

        $marked = $this->service->clearAndReapplyFilter('jack', 9);

        $this->assertEquals(0, $marked);
        $this->assertFalse($readFiltered->isFiltered());
        $this->assertFalse($unreadFiltered->isFiltered());
        $this->assertTrue($unreadFiltered->isUnread());
    }

    public function testSanitizeAndValidateFilterKeywordsNormalizesAndDeduplicates(): void
    {
        $result = $this->service->sanitizeAndValidateFilterKeywords(
            ' Foo, foo ,BAR, bar, baz ',
            null,
            ' /Path , /path '
        );

        $this->assertEquals('Foo, BAR, baz', $result['titleKeywords']);
        $this->assertEquals('', $result['bodyKeywords']);
        $this->assertEquals('/Path', $result['urlKeywords']);
    }

    public function testSanitizeAndValidateFilterKeywordsRejectsOverlongKeyword(): void
    {
        $tooLongKeyword = str_repeat('a', 129);

        $this->expectException(ServiceValidationException::class);
        $this->expectExceptionMessage('exceeds max length');

        $this->service->sanitizeAndValidateFilterKeywords($tooLongKeyword, null, null);
    }

    public function testSanitizeAndValidateFilterKeywordsRejectsTooManyKeywords(): void
    {
        $keywords = implode(',', array_fill(0, 101, 'k'));

        $this->expectException(ServiceValidationException::class);
        $this->expectExceptionMessage('exceeds max keyword count');

        $this->service->sanitizeAndValidateFilterKeywords($keywords, null, null);
    }
}
