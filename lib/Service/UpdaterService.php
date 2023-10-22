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

use OCP\BackgroundJob\IJobList;
use OCA\News\Cron\UpdaterJob;

class UpdaterService
{

    /**
     * @var FolderServiceV2
     */
    private $folderService;

    /**
     * @var FeedServiceV2
     */
    private $feedService;

    /**
     * @var ItemServiceV2
     */
    private $itemService;

    /** @var IJobList */
    private $jobList;

    public function __construct(
        FolderServiceV2 $folderService,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        IJobList $jobList
    ) {
        $this->folderService = $folderService;
        $this->feedService = $feedService;
        $this->itemService = $itemService;
        $this->jobList = $jobList;
    }


    public function beforeUpdate(): void
    {
        $this->folderService->purgeDeleted(null, null);
        $this->feedService->purgeDeleted(null, null);
    }


    public function update(): void
    {
        $this->feedService->fetchAll();
    }


    public function afterUpdate(): void
    {
        $this->itemService->purgeOverThreshold();
    }

    public function reset(): int
    {
        $myJobList = $this->jobList->getJobsIterator(UpdaterJob::class, 1, 0);
        $job = $myJobList->current();

        $this->jobList->resetBackgroundJob($job);

        return 0;
    }
}
