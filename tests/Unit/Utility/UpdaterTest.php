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

namespace OCA\News\Tests\Unit\Utility;

use OCA\News\Utility\Updater;
use PHPUnit\Framework\TestCase;

class UpdaterTest extends TestCase
{

    private $folderService;
    private $feedService;
    private $itemService;
    private $updater;

    protected function setUp() 
    {
        $this->folderService = $this->getMockBuilder(
            '\OCA\News\Service\FolderService'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(
            '\OCA\News\Service\FeedService'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(
            '\OCA\News\Service\ItemService'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->updater = new Updater(
            $this->folderService,
            $this->feedService,
            $this->itemService
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
            ->method('autoPurgeOld');
        $this->updater->afterUpdate();
    }

    public function testUpdate() 
    {
        $this->feedService->expects($this->once())
            ->method('updateAll');
        $this->updater->update();
    }
}