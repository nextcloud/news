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
use \OCP\AppFramework\Http;

use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use OCP\IUserSession;

class FolderController extends Controller
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * @var FolderServiceV2
     */
    private $folderService;

    public function __construct(
        IRequest $request,
        FolderServiceV2 $folderService,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
        $this->folderService = $folderService;
    }


    /**
     * @NoAdminRequired
     *
     * @return array[]
     *
     * @psalm-return array{folders: array}
     */
    public function index(): array
    {
        $folders = $this->folderService->findAllForUser($this->getUserId());
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
            $this->folderService->open($this->getUserId(), $folderId, $open);
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
        $this->folderService->purgeDeleted($this->getUserId(), time() - 600);
        $folder = $this->folderService->create($this->getUserId(), $folderName, $parent);

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
        if (is_null($folderId)) {
            return new JSONResponse([], Http::STATUS_BAD_REQUEST);
        }
        try {
            $this->folderService->markDelete($this->getUserId(), $folderId, true);
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
     * @param int|null $folderId   The ID of the folder
     * @param string   $folderName The new name of the folder
     *
     * @return array|JSONResponse
     */
    public function rename(?int $folderId, string $folderName)
    {
        if (is_null($folderId)) {
            return new JSONResponse([], Http::STATUS_BAD_REQUEST);
        }
        try {
            $folder = $this->folderService->rename($this->getUserId(), $folderId, $folderName);

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
     * @param int      $maxItemId
     *
     * @return void
     *
     * @throws ServiceConflictException
     * @throws ServiceNotFoundException
     */
    public function read(?int $folderId, int $maxItemId): void
    {
        $folderId = $folderId === 0 ? null : $folderId;

        $this->folderService->read($this->getUserId(), $folderId, $maxItemId);
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
            $this->folderService->markDelete($this->getUserId(), $folderId, false);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        }

        return [];
    }
}
