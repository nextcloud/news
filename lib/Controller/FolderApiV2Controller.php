<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\ItemServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

class FolderApiV2Controller extends ApiController
{
    use ApiV2ResponseTrait;

    private $folderService;
    private $itemService;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        FolderServiceV2 $folderService,
        ItemServiceV2 $itemService
    ) {
        parent::__construct($request, $userSession);

        $this->folderService = $folderService;
        $this->itemService = $itemService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param string $name
     * @return array|mixed|\OCP\AppFramework\Http\JSONResponse
     */
    public function createFolder($name)
    {
        try {
            $this->folderService->purgeDeleted($this->getUserId(), false);
            $responseData = $this->serialize(
                $this->folderService->create($this->getUserId(), $name)
            );
            return $this->response([
                'folder' => $responseData
            ]);
        } catch (ServiceValidationException $ex) {
            return $this->errorResponse($ex, Http::STATUS_BAD_REQUEST);
        } catch (ServiceConflictException $ex) {
            return $this->errorResponse($ex, Http::STATUS_CONFLICT);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     * @param int    $folderId
     * @param string $name
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function updateFolder($folderId, $name)
    {
        $response = null;
        try {
            $response = $this->folderService->rename($this->getUserId(), $folderId, $name);
        } catch (ServiceValidationException $ex) {
            return $this->errorResponse($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        } catch (ServiceConflictException $ex) {
            return $this->errorResponse($ex, Http::STATUS_CONFLICT);
        } catch (ServiceNotFoundException $ex) {
            return $this->errorResponse($ex, Http::STATUS_NOT_FOUND);
        }

        return $this->response([
            'folder' => $response
        ]);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function deleteFolder($folderId)
    {
        try {
            $responseData = $this->serialize(
                $this->folderService->delete($this->getUserId(), $folderId)
            );
            return $this->response([
                'folder' => $responseData
            ]);
        } catch (ServiceNotFoundException $ex) {
            return $this->errorResponse($ex, Http::STATUS_NOT_FOUND);
        }
    }
}
