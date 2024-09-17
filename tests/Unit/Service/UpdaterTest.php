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
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\UpdaterService;
use PHPUnit\Framework\TestCase;
use OCP\BackgroundJob\IJobList;
use OCP\BackgroundJob\IJob;

class UpdaterTest extends TestCase
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
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;

    /**
     * @var UpdaterService
     */
    private $updater;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IJobList
     */
    private $jobList;

    protected function setUp(): void
    {
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jobList = $this->getMockBuilder(IJobList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updater = new UpdaterService(
            $this->folderService,
            $this->feedService,
            $this->itemService,
            $this->jobList
        );
    }

    public function testBeforeUpdate()
    {
        $this->folderService->expects($this->once())
            ->method('purgeDeleted');
        $this->feedService->expects($this->once())
            ->method('purgeDeleted');
        $this->updater->beforeUpdate();
    }


    public function testAfterUpdate()
    {
        $this->itemService->expects($this->once())
            ->method('purgeOverThreshold');
        $this->updater->afterUpdate();
    }

    public function testUpdate()
    {
        $this->feedService->expects($this->once())
            ->method('fetchAll');
        $this->updater->update();
    }
}
