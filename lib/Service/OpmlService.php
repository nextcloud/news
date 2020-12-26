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

use OCA\News\Utility\OPMLExporter;

class OpmlService
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
     * @var OPMLExporter
     */
    private $exporter;

    public function __construct(
        FolderServiceV2 $folderService,
        FeedServiceV2 $feedService,
        OPMLExporter $exporter
    ) {
        $this->folderService = $folderService;
        $this->feedService = $feedService;
        $this->exporter = $exporter;
    }

    /**
     * Export all feeds for a user.
     *
     * @param string $userId User ID
     *
     * @return string Exported OPML data
     */
    public function export(string $userId): string
    {
        $feeds   = $this->feedService->findAllForUser($userId);
        $folders = $this->folderService->findAllForUser($userId);

        return $this->exporter->build($folders, $feeds)
                              ->saveXML();
    }
}
