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


namespace OCA\News\Utility;

use \OCA\News\Service\FolderService;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;

class Updater
{

    /**
     * @var FolderService
     */
    private $folderService;

    /**
     * @var FeedService
     */
    private $feedService;

    /**
     * @var ItemService
     */
    private $itemService;

    public function __construct(
        FolderService $folderService,
        FeedService $feedService,
        ItemService $itemService
    ) {
        $this->folderService = $folderService;
        $this->feedService = $feedService;
        $this->itemService = $itemService;
    }


    public function beforeUpdate()
    {
        $this->folderService->purgeDeleted();
        $this->feedService->purgeDeleted();
    }


    public function update()
    {
        $this->feedService->updateAll();
    }


    public function afterUpdate()
    {
        $this->itemService->autoPurgeOld();
    }
}
