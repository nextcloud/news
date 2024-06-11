<?php

namespace OCA\News\Search;

use OCA\News\Db\Feed;
use OCA\News\Service\FeedServiceV2;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\Search\IFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FeedSearchProviderTest extends TestCase
{

    /**
     * @var MockObject|FeedServiceV2
     */
    private $folderService;

    /**
     * @var MockObject|IL10N
     */
    private $l10n;

    /**
     * @var MockObject|IURLGenerator
     */
    private $generator;

    /**
     * @var FeedSearchProvider
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
        $this->folderService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->class = new FeedSearchProvider(
            $this->l10n,
            $this->generator,
            $this->folderService
        );
    }

    public function testGetId()
    {
        $this->assertSame('news_feed', $this->class->getId());
    }

    public function testGetName()
    {
        $this->l10n->expects($this->once())
                   ->method('t')
                   ->with('News feeds')
                   ->willReturnArgument(0);

        $this->assertSame('News feeds', $this->class->getName());
    }

    public function testGetOrderExternal()
    {
        $this->assertSame(60, $this->class->getOrder('contacts.Page.index', []));
    }

    public function testGetOrderInternal()
    {
        $this->assertSame(-1, $this->class->getOrder('news.page.index', []));
    }

    public function testSearch()
    {
        $user = $this->getMockBuilder(IUser::class)
                     ->getMock();
        $query = $this->getMockBuilder(ISearchQuery::class)
                     ->getMock();

        $term = $this->getMockBuilder(IFilter::class)
                     ->getMock();

        $user->expects($this->once())
              ->method('getUID')
              ->willReturn('user');

        $query->expects($this->once())
              ->method('getFilter')
              ->with('term')
              ->willReturn($term);

        $term->expects($this->once())
             ->method('get')
             ->willReturn('some_term');

        $folders = [
            Feed::fromRow(['id' => 1,'title' => 'some_tErm', 'unread_count'=> 1]),
            Feed::fromRow(['id' => 2,'title' => 'nothing', 'unread_count'=> 1])
        ];

        $this->folderService->expects($this->once())
                            ->method('findAllForUser')
                            ->with('user')
                            ->willReturn($folders);

        $this->l10n->expects($this->atLeast(2))
                   ->method('t')
                   ->willReturnArgument(0);

        $this->generator->expects($this->once())
                        ->method('imagePath')
                        ->with('core', 'rss.svg')
                        ->willReturn('folderpath.svg');

        $this->generator->expects($this->once())
                        ->method('linkToRoute')
                        ->with('news.page.index')
                        ->willReturn('/news');


        $result = $this->class->search($user, $query)->jsonSerialize();
        $entry = $result['entries'][0]->jsonSerialize();
        $this->assertSame('News', $result['name']);
        $this->assertSame('some_tErm', $entry['title']);
        $this->assertSame('folderpath.svg', $entry['thumbnailUrl']);
        $this->assertSame('Unread articles: 1', $entry['subline']);
        $this->assertSame('/news#/feed/1', $entry['resourceUrl']);
    }
}
