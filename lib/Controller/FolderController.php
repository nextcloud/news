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

namespace OCA\News\Controller;

use OCA\News\Service\Exceptions\ServiceException;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FolderService;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

class FolderController extends Controller
{
    use JSONHttpErrorTrait;

    private $folderService;
    private $feedService;
    private $itemService;
    private $userId;

    public function __construct(
        string $appName,
        IRequest $request,
        FolderService $folderService,
        FeedService $feedService,
        ItemService $itemService,
        $UserId
    ) {
        parent::__construct($appName, $request);
        $this->folderService = $folderService;
        $this->feedService = $feedService;
        $this->itemService = $itemService;
        $this->userId = $UserId;
    }


    /**
     * @NoAdminRequired
     */
    public function index()
    {
        $folders = $this->folderService->findAllForUser($this->userId);
        return ['folders' => $folders];
    }


    /**
     * @NoAdminRequired
     *
     * @param int  $folderId
     * @param bool $open
     *
     * @return array|JSONResponse
     */
    public function open(int $folderId, bool $open)
    {
        try {
            $this->folderService->open($folderId, $open, $this->userId);
        } catch (ServiceException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param string $folderName
     *
     * @return array|JSONResponse
     */
    public function create(string $folderName)
    {
        try {
            // we need to purge deleted folders if a folder is created to
            // prevent already exists exceptions
            $this->folderService->purgeDeleted($this->userId, false);
            $folder = $this->folderService->create($folderName, $this->userId);

            return ['folders' => [$folder]];
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceValidationException $ex) {
            return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     *
     * @return array|JSONResponse
     */
    public function delete(int $folderId)
    {
        try {
            $this->folderService->markDeleted($folderId, $this->userId);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param string $folderName
     * @param int    $folderId
     *
     * @return array|JSONResponse
     */
    public function rename(string $folderName, int $folderId)
    {
        try {
            $folder = $this->folderService->rename(
                $folderId,
                $folderName,
                $this->userId
            );

            return ['folders' => [$folder]];
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceValidationException $ex) {
            return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }

    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     * @param int $highestItemId
     * @return array
     */
    public function read(int $folderId, int $highestItemId): array
    {
        $this->itemService->readFolder(
            $folderId,
            $highestItemId,
            $this->userId
        );

        return ['feeds' => $this->feedService->findAllForUser($this->userId)];
    }


    /**
     * @NoAdminRequired
     *
     * @param int $folderId
     *
     * @return array|JSONResponse
     */
    public function restore(int $folderId)
    {
        try {
            $this->folderService->unmarkDeleted($folderId, $this->userId);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }
}
