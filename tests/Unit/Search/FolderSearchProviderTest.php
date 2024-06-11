<?php

namespace OCA\News\Search;

use OCA\News\Db\Folder;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\OpmlService;
use OCA\News\Utility\OPMLExporter;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\Search\IFilter;
use PHPUnit\Framework\TestCase;

class FolderSearchProviderTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FolderServiceV2
     */
    private $folderService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IL10N
     */
    private $l10n;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IURLGenerator
     */
    private $generator;

    /**
     * @var FolderSearchProvider
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
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->class = new FolderSearchProvider(
            $this->l10n,
            $this->generator,
            $this->folderService
        );
    }

    public function testGetId()
    {
        $this->assertSame('news_folder', $this->class->getId());
    }

    public function testGetName()
    {
        $this->l10n->expects($this->once())
                   ->method('t')
                   ->with('News folders')
                   ->willReturnArgument(0);

        $this->assertSame('News folders', $this->class->getName());
    }

    public function testGetOrderExternal()
    {
        $this->assertSame(55, $this->class->getOrder('contacts.Page.index', []));
    }

    public function testGetOrderInternal()
    {
        $this->assertSame(0, $this->class->getOrder('news.page.index', []));
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
            Folder::fromRow(['id' => 1,'name' => 'some_tErm']),
            Folder::fromRow(['id' => 2,'name' => 'nothing'])
        ];

        $this->folderService->expects($this->once())
                            ->method('findAllForUser')
                            ->with('user')
                            ->willReturn($folders);

        $this->l10n->expects($this->once())
            ->method('t')
            ->with('News')
            ->willReturnArgument(0);

        $this->generator->expects($this->once())
                        ->method('imagePath')
                        ->with('core', 'filetypes/folder.svg')
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
        $this->assertSame('', $entry['subline']);
        $this->assertSame('/news#/folder/1', $entry['resourceUrl']);
    }
}
