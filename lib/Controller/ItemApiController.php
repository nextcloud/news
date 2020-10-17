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
use \OCA\News\Service\Exceptions\ServiceNotFoundException;

class ItemApiController extends ApiController
{
    use JSONHttpErrorTrait;

    private $itemService;
    private $serializer;

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        ItemService $itemService
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->itemService = $itemService;
        $this->serializer = new EntityApiSerializer('items');
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int  $type
     * @param int  $id
     * @param bool $getRead
     * @param int  $batchSize
     * @param int  $offset
     * @param bool $oldestFirst
     * @return array|mixed
     */
    public function index(
        int $type = 3,
        int $id = 0,
        bool $getRead = true,
        int $batchSize = -1,
        int $offset = 0,
        bool $oldestFirst = false
    ) {
        return $this->serializer->serialize(
            $this->itemService->findAllItems(
                $id,
                $type,
                $batchSize,
                $offset,
                $getRead,
                $oldestFirst,
                $this->getUserId()
            )
        );
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $type
     * @param int $id
     * @param int $lastModified
     * @return array|mixed
     */
    public function updated($type = 3, $id = 0, $lastModified = 0)
    {
        // needs to be turned into a millisecond timestamp to work properly
        if (strlen((string) $lastModified) <= 10) {
            $paddedLastModified = $lastModified . '000000';
        } else {
            $paddedLastModified = $lastModified;
        }
        return $this->serializer->serialize(
            $this->itemService->findAllNew(
                $id,
                $type,
                $paddedLastModified,
                true,
                $this->getUserId()
            )
        );
    }


    private function setRead($isRead, $itemId)
    {
        try {
            $this->itemService->read($itemId, $isRead, $this->getUserId());
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
     * @param int $itemId
     *
     * @return array|JSONResponse
     */
    public function read(int $itemId)
    {
        return $this->setRead(true, $itemId);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $itemId
     *
     * @return array|JSONResponse
     */
    public function unread(int $itemId)
    {
        return $this->setRead(false, $itemId);
    }


    private function setStarred($isStarred, $feedId, $guidHash)
    {
        try {
            $this->itemService->star(
                $feedId,
                $guidHash,
                $isStarred,
                $this->getUserId()
            );
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    private function setStarredById($isStarred, $itemId)
    {
        try {
            $this->itemService->starById(
                $itemId,
                $isStarred,
                $this->getUserId()
            );
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
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return array|JSONResponse
     */
    public function star(int $feedId, string $guidHash)
    {
        return $this->setStarred(true, $feedId, $guidHash);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int    $itemId
     *
     * @return array|JSONResponse
     */
    public function starById(int $itemId)
    {
        return $this->setStarredById(true, $itemId);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return array|JSONResponse
     */
    public function unstar(int $feedId, string $guidHash)
    {
        return $this->setStarred(false, $feedId, $guidHash);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int    $itemId
     *
     * @return array|JSONResponse
     */
    public function unstarById(int $itemId)
    {
        return $this->setStarredById(false, $itemId);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $newestItemId
     */
    public function readAll(int $newestItemId)
    {
        $this->itemService->readAll($newestItemId, $this->getUserId());
    }


    private function setMultipleRead($isRead, $items)
    {
        foreach ($items as $id) {
            try {
                $this->itemService->read($id, $isRead, $this->getUserId());
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function readMultiple(array $items)
    {
        $this->setMultipleRead(true, $items);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function unreadMultiple(array $items)
    {
        $this->setMultipleRead(false, $items);
    }


    private function setMultipleStarred($isStarred, $items)
    {
        foreach ($items as $item) {
            try {
                $this->itemService->star(
                    $item['feedId'],
                    $item['guidHash'],
                    $isStarred,
                    $this->getUserId()
                );
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }


    private function setMultipleStarredById($isStarred, $items)
    {
        foreach ($items as $id) {
            try {
                $this->itemService->starById($id, $isStarred, $this->getUserId());
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function starMultiple(array $items)
    {
        $this->setMultipleStarred(true, $items);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function starMultipleById(array $items)
    {
        $this->setMultipleStarredById(true, $items);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function unstarMultiple(array $items)
    {
        $this->setMultipleStarred(false, $items);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int[] $items item ids
     */
    public function unstarMultipleById(array $items)
    {
        $this->setMultipleStarredById(false, $items);
    }

}
