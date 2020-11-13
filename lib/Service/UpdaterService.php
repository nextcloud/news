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


namespace OCA\News\Service;

class UpdaterService
{

    /**
     * @var FolderServiceV2
     */
    private $folderService;

    /**
     * @var FeedService
     */
    private $feedService;

    /**
     * @var ItemServiceV2
     */
    private $itemService;

    public function __construct(
        FolderServiceV2 $folderService,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService
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
        $this->feedService->fetchAll();
    }


    public function afterUpdate()
    {
        $this->itemService->purgeOverThreshold(null);
    }
}
