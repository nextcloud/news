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

use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Utility\OPMLExporter;
use OCA\News\Utility\OPMLImporter;
use OCA\News\Db\Folder;
use Psr\Log\LoggerInterface;

class OpmlService
{
    public function __construct(
        private FolderServiceV2 $folderService,
        private FeedServiceV2 $feedService,
        private OPMLExporter $exporter,
        private OPMLImporter $importer,
        private LoggerInterface $logger,
    ) {
        //NO-OP
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

    /**
     * Import all feeds and folders for a user.
     *
     * @param string $userId User ID
     * @param string $data OPML data
     *
     * @return bool Status of the import
     */
    public function import(string $userId, string $data): bool
    {
        list($folders, $feeds) = $this->importer->import($userId, $data);

        $error = 0;
        $folderEntities  = [];
        $dbFolders = $this->folderService->findAllForUser($userId);
        foreach ($folders as $folder) {
            $existing = array_filter($dbFolders, fn(Folder $dbFolder) => $dbFolder->getName() === $folder['name']);

            if (count($existing) > 0) {
                $folderEntities[$folder['name']] = array_pop($existing);
                continue;
            }

            $folderEntities[$folder['name']] = $this->folderService->create(
                $userId,
                $folder['name'],
                $folderEntities[$folder['parentName']] ?? null,
            );
        }

        foreach ($feeds as $feed) {
            if ($this->feedService->existsForUser($userId, $feed['url'])) {
                continue;
            }
            $parent = $folderEntities[$feed['folder']] ?? null;
            try {
                $this->feedService->create(
                    $userId,
                    $feed['url'],
                    $parent?->getId(),
                    full_text: false,
                    title: $feed['title'],
                    full_discover: false,
                );
            } catch (ServiceNotFoundException | ServiceConflictException $e) {
                $error++;
                $this->logger->warning('Could not import feed ' . $feed['url']);
            }
        }

        if ($error > 0) {
            throw new ServiceValidationException("Failed to import $error feeds. Please check the server log!");
        }

        return true;
    }
}
