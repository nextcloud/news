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

use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use OCP\IUser;

class FolderController extends Controller
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * @var FolderServiceV2
     */
    private $folderService;
    //TODO: Remove
    private $feedService;
    //TODO: Remove
    private $itemService;
    private $userId;

    public function __construct(
        string $appName,
        IRequest $request,
        FolderServiceV2 $folderService,
        FeedService $feedService,
        ItemService $itemService,
        IUser $user
    ) {
        parent::__construct($appName, $request);
        $this->folderService = $folderService;
        $this->feedService = $feedService;
        $this->itemService = $itemService;
        $this->userId = $user->getUID();
    }


    /**
     * @NoAdminRequired
     */
    public function index()
    {
        $folders = $this->folderService->findAllForUser($this->userId);
        return ['folders' => $this->serialize($folders)];
    }


    /**
     * @NoAdminRequired
     *
     * @param int|null $folderId
     * @param bool     $open
     *
     * @return array|JSONResponse
     */
    public function open(?int $folderId, bool $open)
    {
        $folderId = $folderId === 0 ? null : $folderId;

        try {
            $this->folderService->open($this->userId, $folderId, $open);
        } catch (ServiceException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param string   $folderName
     * @param int|null $parent
     *
     * @return array|JSONResponse
     */
    public function create(string $folderName, ?int $parent = null)
    {
        $this->folderService->purgeDeleted();
        $folder = $this->folderService->create($this->userId, $folderName, $parent);

        return ['folders' => $this->serialize($folder)];
    }


    /**
     * @NoAdminRequired
     *
     * @param int|null $folderId
     *
     * @return array|JSONResponse
     */
    public function delete(?int $folderId)
    {
        if (empty($folderId)) {
            return new JSONResponse([], Http::STATUS_BAD_REQUEST);
        }
        try {
            $this->folderService->markDelete($this->userId, $folderId, true);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param string   $folderName
     * @param int|null $folderId
     *
     * @return array|JSONResponse
     */
    public function rename(string $folderName, ?int $folderId)
    {
        if (empty($folderId)) {
            return new JSONResponse([], Http::STATUS_BAD_REQUEST);
        }
        try {
            $folder = $this->folderService->rename($this->userId, $folderId, $folderName);

            return ['folders' => $this->serialize($folder)];
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }

    /**
     * @NoAdminRequired
     *
     * @param int|null $folderId
     * @param int      $highestItemId
     *
     * @return array
     */
    public function read(?int $folderId, int $highestItemId): array
    {
        $folderId = $folderId === 0 ? null : $folderId;

        $this->itemService->readFolder(
            $folderId,
            $highestItemId,
            $this->userId
        );
        $feeds = $this->feedService->findAllForUser($this->userId);
        return ['feeds' => $this->serialize($feeds)];
    }


    /**
     * @NoAdminRequired
     *
     * @param int|null $folderId
     *
     * @return array|JSONResponse
     */
    public function restore(?int $folderId)
    {
        $folderId = $folderId === 0 ? null : $folderId;

        try {
            $this->folderService->markDelete($this->userId, $folderId, false);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        }

        return [];
    }
}
