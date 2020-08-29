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

use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FolderService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

class FolderApiController extends ApiController
{
    use JSONHttpError;

    private $folderService;
    private $itemService;
    private $serializer;

    public function __construct(
        $appName,
        IRequest $request,
        IUserSession $userSession,
        FolderService $folderService,
        ItemService $itemService
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->folderService = $folderService;
        $this->itemService = $itemService;
        $this->serializer = new EntityApiSerializer('folders');
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index()
    {
        return $this->serializer->serialize(
            $this->folderService->findAll($this->getUserId())
        );
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
        try {
            $this->folderService->purgeDeleted($this->getUserId(), false);
            return $this->serializer->serialize(
                $this->folderService->create($name, $this->getUserId())
            );
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
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function delete($folderId)
    {
        try {
            $this->folderService->delete($folderId, $this->getUserId());
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
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
        try {
            $this->folderService->rename($folderId, $name, $this->getUserId());
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
     * @param int $folderId
     * @param int $newestItemId
     */
    public function read($folderId, $newestItemId)
    {
        $this->itemService->readFolder($folderId, $newestItemId, $this->getUserId());
    }
}
