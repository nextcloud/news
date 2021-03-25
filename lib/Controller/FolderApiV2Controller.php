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

class FolderApiV2Controller extends ApiController
{
    use ApiPayloadTrait;
    use JSONHttpErrorTrait;

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
    public function create($name)
    {
        if (empty($name)) {
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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     * @param int    $folderId
     * @param string $name
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function update($folderId, $name)
    {
        if (empty($name)) {
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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function delete($folderId)
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
