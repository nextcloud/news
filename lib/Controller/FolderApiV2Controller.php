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
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\CORS;

use \OCA\News\Service\FolderServiceV2;
use \OCA\News\Service\ItemServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;

class FolderApiV2Controller extends ApiController
{
    use ApiPayloadTrait;
    use JSONHttpErrorTrait;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        private FolderServiceV2 $folderService,
        private ItemServiceV2 $itemService
    ) {
        parent::__construct($request, $userSession);
    }

    /**
     * @param string $name
     * @return array|mixed|\OCP\AppFramework\Http\JSONResponse
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function create(string $name)
    {
        if (trim($name) === '') {
            return $this->errorResponseV2('folder name is empty', 1, Http::STATUS_BAD_REQUEST);
        }

        $this->folderService->purgeDeleted($this->getUserId(), false);
        $responseData = $this->serializeEntityV2(
            $this->folderService->create($this->getUserId(), $name)
        );
        return $this->responseV2([
            'folder' => $responseData
        ]);
    }

    /**
     * @param int    $folderId
     * @param string $name
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function update(int $folderId, string $name)
    {
        if (trim($name) === '') {
            return $this->errorResponseV2('folder name is empty', 1, Http::STATUS_BAD_REQUEST);
        }

        $response = null;
        try {
            $response = $this->folderService->rename($this->getUserId(), $folderId, $name);
        } catch (ServiceNotFoundException $ex) {
            return $this->errorResponseWithExceptionV2($ex, Http::STATUS_NOT_FOUND);
        }

        return $this->responseV2([
            'folder' => $response
        ]);
    }


    /**
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function delete(int $folderId)
    {
        try {
            $responseData = $this->serializeEntityV2(
                $this->folderService->delete($this->getUserId(), $folderId)
            );
            return $this->responseV2([
                'folder' => $responseData
            ]);
        } catch (ServiceNotFoundException $ex) {
            return $this->errorResponseWithExceptionV2($ex, Http::STATUS_NOT_FOUND);
        }
    }
}
