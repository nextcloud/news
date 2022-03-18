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

use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

class FolderApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * @var FolderServiceV2
     */
    private $folderService;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        FolderServiceV2 $folderService
    ) {
        parent::__construct($request, $userSession);

        $this->folderService = $folderService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index(): array
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
            $this->folderService->purgeDeleted($this->getUserId(), time() - 600);
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
        if (is_null($folderId)) {
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
        if (is_null($folderId)) {
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
     * @param int|null $folderId  ID of the folder
     * @param int      $newestItemId The newest read item
     */
    public function read(?int $folderId, int $newestItemId): void
    {
        $folderId = $folderId === 0 ? null : $folderId;

        $this->folderService->read($this->getUserId(), $folderId, $newestItemId);
    }
}
