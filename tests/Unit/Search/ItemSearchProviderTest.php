<?php

namespace OCA\News\Search;

use OCA\News\Db\Item;
use OCA\News\Db\ListType;
use OCA\News\Service\ItemServiceV2;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\Search\IFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemSearchProviderTest extends TestCase
{

    /**
     * @var MockObject|ItemServiceV2
     */
    private $itemService;

    /**
     * @var MockObject|IL10N
     */
    private $l10n;

    /**
     * @var MockObject|IURLGenerator
     */
    private $generator;

    /**
     * @var ItemSearchProvider
     */
    private $class;

    protected function setUp(): void
    {
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generator = $this->getMockBuilder(IURLGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->class = new ItemSearchProvider(
            $this->l10n,
            $this->generator,
            $this->itemService
        );
    }

    public function testGetId()
    {
        $this->assertSame('news_item', $this->class->getId());
    }

    public function testGetName()
    {
        $this->l10n->expects($this->once())
                   ->method('t')
                   ->with('News articles')
                   ->willReturnArgument(0);

        $this->assertSame('News articles', $this->class->getName());
    }

    public function testGetOrderExternal()
    {
        $this->assertSame(65, $this->class->getOrder('contacts.Page.index', []));
    }

    public function testGetOrderInternal()
    {
        $this->assertSame(1, $this->class->getOrder('news.page.index', []));
    }

    public function testSearch()
    {
        $user = $this->getMockBuilder(IUser::class)
                     ->getMock();
        $query = $this->getMockBuilder(ISearchQuery::class)
                     ->getMock();

        $term = $this->getMockBuilder(IFilter::class)
                      ->getMock();

        $query->expects($this->once())
                ->method('getCursor')
                ->willReturn(null);

        $query->expects($this->once())
                ->method('getLimit')
                ->willReturn(10);

        $user->expects($this->once())
                ->method('getUID')
                ->willReturn('user');

        $query->expects($this->once())
              ->method('getFilter')
              ->with('term')
              ->willReturn($term);

        $term->expects($this->once())
             ->method('get')
             ->willReturn('some text');


        $items = [
            Item::fromRow(['id' => 1,'title' => 'some_tErm', 'body' => 'some text', 'feedId' => 1]),
            Item::fromRow(['id' => 2,'title' => 'nothing', 'body' => 'some text', 'feedId' => 1])
        ];

        $this->itemService->expects($this->once())
                            ->method('findAllWithFilters')
                            ->with(
                                'user',
                                ListType::ALL_ITEMS,
                                10,
                                0,
                                false,
                                ['some text']
                            )
                            ->willReturn($items);


        $this->l10n->expects($this->once())
            ->method('t')
            ->with('News')
            ->willReturnArgument(0);

        $this->generator->expects($this->once())
                        ->method('imagePath')
                        ->with('core', 'filetypes/text.svg')
                        ->willReturn('folderpath.svg');

        $this->generator->expects($this->exactly(2))
                        ->method('linkToRoute')
                        ->with('news.page.index')
                        ->willReturn('/news');


        $result = $this->class->search($user, $query)->jsonSerialize();
        $entry = $result['entries'][0]->jsonSerialize();
        $this->assertSame('News', $result['name']);
        $this->assertSame('some_tErm', $entry['title']);
        $this->assertSame('folderpath.svg', $entry['thumbnailUrl']);
        $this->assertSame('some text', $entry['subline']);
        $this->assertSame('/news#/feed/1', $entry['resourceUrl']);
    }
}
