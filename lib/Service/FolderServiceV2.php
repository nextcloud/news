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

use OC\AppFramework\Utility\TimeFactory;
use OCA\News\Db\Folder;
use OCA\News\Db\FolderMapperV2;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\Entity;
use Psr\Log\LoggerInterface;

/**
 * Class FolderService
 *
 * @package OCA\News\Service
 */
class FolderServiceV2 extends Service
{
    public function __construct(
        FolderMapperV2 $mapper,
        LoggerInterface $logger,
        private FeedServiceV2 $feedService,
        private TimeFactory $timeFactory,
    ) {
        parent::__construct($mapper, $logger);
    }

    /**
     * Finds all folders of a user
     *
     * @param string $userId The name/ID of the user
     * @param array  $params Filter parameters
     *
     * @return Folder[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }

    /**
     * Find all folders and it's feeds.
     *
     * @param string $userId The name/ID of the owner
     *
     * @return Folder[]
     */
    public function findAllForUserRecursive(string $userId): array
    {
        $folders = $this->findAllForUser($userId);
        foreach ($folders as $folder) {
            $feeds = $this->feedService->findAllFromFolder($folder->getId());
            $folder->feeds = $feeds;
        }

        return $folders;
    }

    /**
     * Find all folders.
     *
     * @return Folder[]
     */
    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    /**
     * Create a folder
     *
     * @param string   $userId
     * @param string   $name
     * @param int|null $parent
     *
     * @return Folder
     */
    public function create(string $userId, string $name, ?int $parent = null): Entity
    {
        $folder = new Folder();
        $folder->setUserId($userId)
               ->setName($name)
               ->setParentId($parent)
               ->setOpened(true);

        return $this->mapper->insert($folder);
    }

    /**
     * Purge all deleted folders.
     *
     * @param string|null $userID       The user to purge
     * @param int|null    $minTimestamp The timestamp to purge from
     *
     * @return void
     */
    public function purgeDeleted(?string $userID, ?int $minTimestamp): void
    {
        $this->mapper->purgeDeleted($userID, $minTimestamp);
    }

    /**
     * Rename a folder
     *
     * @param string $userId   Folder owner
     * @param int    $folderId Folder ID
     * @param string $newName  New name
     *
     * @return Folder
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function rename(string $userId, int $folderId, string $newName): Entity
    {
        $folder = $this->find($userId, $folderId);
        $folder->setName($newName);

        return $this->mapper->update($folder);
    }

    /**
     * Mark a folder as deleted
     *
     * @param string $userId   Folder owner
     * @param int    $folderId Folder ID
     * @param bool   $mark     If the mark should be added or removed
     *
     * @return Folder
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function markDelete(string $userId, int $folderId, bool $mark): Entity
    {
        $folder = $this->find($userId, $folderId);
        $time = $mark ? $this->timeFactory->now()->getTimestamp() : 0;
        $folder->setDeletedAt($time);

        return $this->mapper->update($folder);
    }

    /**
     * Mark a folder as opened
     *
     * @param string   $userId   Folder owner
     * @param int|null $folderId Folder ID
     * @param bool     $open     If the mark should be added or removed
     *
     * @return Folder
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function open(string $userId, ?int $folderId, bool $open): Entity
    {
        $folder = $this->find($userId, $folderId);
        $folder->setOpened($open);
        return $this->mapper->update($folder);
    }

    /**
     * Mark a folder as read
     *
     * @param string   $userId    Folder owner
     * @param int      $id        Folder ID
     * @param int|null $newestItemId Highest item ID to mark as read
     *
     * @return int
     *
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function read(string $userId, int $id, ?int $newestItemId = null): int
    {
        $folder = $this->find($userId, $id);

        return $this->mapper->read($userId, $folder->getId(), $newestItemId);
    }
}
