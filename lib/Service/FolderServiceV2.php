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

use OCA\News\Db\Feed;
use OCA\News\Db\FeedMapperV2;
use OCA\News\Db\Folder;
use OCA\News\Db\FolderMapperV2;
use Psr\Log\LoggerInterface;

/**
 * Class FolderService
 *
 * @package OCA\News\Service
 */
class FolderServiceV2 extends Service
{
    /**
     * @var FeedServiceV2
     */
    private $feedService;

    public function __construct(
        FolderMapperV2 $mapper,
        FeedServiceV2 $feedService,
        LoggerInterface $logger
    ) {
        parent::__construct($mapper, $logger);
        $this->feedService = $feedService;
    }

    /**
     * Finds all folders of a user
     *
     * @param string $userId the name of the user
     *
     * @return Folder[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId);
    }

    /**
     * @param string $userId
     *
     * @return Folder[]
     */
    public function findAllForUserRecursive(string $userId): array
    {
        $folders = $this->findAllForUser($userId);
        foreach ($folders as &$folder) {
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

    public function create(string $userId, string $name, int $parent = 0): void
    {
        $folder = new Folder();
        $folder->setUserId($userId);
        $folder->setName($name);
        $folder->setParentId($parent);

        $this->mapper->insert($folder);
    }

    public function delete(string $user, int $id)
    {
        $entity = $this->mapper->findFromUser($user, $id);

        $this->mapper->delete($entity);
    }

    public function purgeDeleted()
    {
        $this->mapper->purgeDeleted();
    }
}
