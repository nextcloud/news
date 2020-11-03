<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    David Guillot <david@guillot.me>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2018 David Guillot
 */

namespace OCA\News\Controller;

use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\ItemService;
use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

class FolderApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    private $folderService;
    //TODO: Remove
    private $itemService;

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        FolderServiceV2 $folderService,
        ItemService $itemService
    ) {
        parent::__construct($appName, $request, $userSession);

        $this->folderService = $folderService;
        $this->itemService = $itemService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index()
    {
        $folders = $this->folderService->findAllForUser($this->getUserId());
        return ['folders' => $this->serialize($folders)];
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param string $name
     *
     * @return array|mixed|JSONResponse
     */
    public function create(string $name)
    {
        try {
            $this->folderService->purgeDeleted();
            $folder = $this->folderService->create($this->getUserId(), $name);
            return ['folders' => $this->serialize($folder)];
        } catch (ServiceValidationException $ex) {
            return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        }
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
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
            $this->folderService->delete($this->getUserId(), $folderId);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int|null $folderId
     * @param string   $name
     *
     * @return array|JSONResponse
     */
    public function update(?int $folderId, string $name)
    {
        if (empty($folderId)) {
            return new JSONResponse([], Http::STATUS_BAD_REQUEST);
        }

        try {
            $this->folderService->rename($this->getUserId(), $folderId, $name);
        } catch (ServiceValidationException $ex) {
            return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int|null $folderId
     * @param int      $newestItemId
     */
    public function read(?int $folderId, int $newestItemId): void
    {
        if ($folderId === 0) {
            $folderId = null;
        }
        $this->itemService->readFolder($folderId, $newestItemId, $this->getUserId());
    }
}
