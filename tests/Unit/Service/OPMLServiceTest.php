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

use FeedIo\Explorer;
use FeedIo\Reader\ReadErrorException;

use OC\L10N\L10N;
use OCA\News\Db\FeedMapperV2;
use OCA\News\Db\Folder;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ImportService;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\OpmlService;
use OCA\News\Utility\OPMLExporter;
use OCA\News\Utility\Time;
use OCP\AppFramework\Db\DoesNotExistException;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;
use OCP\IConfig;
use OCP\IL10N;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


class OPMLServiceTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FolderServiceV2
     */
    private $folderService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OPMLExporter
     */
    private $exporter;

    /** @var OpmlService */
    private $class;

    /**
     * @var string
     */
    private $uid;

    protected function setUp(): void
    {
        $this->exporter = $this->getMockBuilder(OPMLExporter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderService = $this
            ->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 333333;

        $this->class = new OpmlService(
            $this->folderService,
            $this->feedService,
            $this->exporter
        );
        $this->uid = 'jack';
    }

    public function testExportEmpty()
    {
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->will($this->returnValue([]));
        $this->folderService->expects($this->once())
            ->method('findAllForUser')
            ->will($this->returnValue([]));

        $domdoc = $this->getMockBuilder(\DOMDocument::class)
                       ->getMock();

        $this->exporter->expects($this->once())
            ->method('build')
            ->with([], [])
            ->will($this->returnValue($domdoc));

        $domdoc->expects($this->once())
            ->method('saveXML')
            ->will($this->returnValue('doc'));

        $this->assertEquals('doc', $this->class->export('jack'));
    }

    public function testExportSuccess()
    {
        $feed = Feed::fromParams(['id' => 1]);
        $folder = Folder::fromParams(['id' => 1]);
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->will($this->returnValue([$feed]));
        $this->folderService->expects($this->once())
            ->method('findAllForUser')
            ->will($this->returnValue([$folder]));

        $domdoc = $this->getMockBuilder(\DOMDocument::class)
                       ->getMock();

        $this->exporter->expects($this->once())
            ->method('build')
            ->with([$folder], [$feed])
            ->will($this->returnValue($domdoc));

        $domdoc->expects($this->once())
            ->method('saveXML')
            ->will($this->returnValue('doc'));

        $this->assertEquals('doc', $this->class->export('jack'));
    }

}
