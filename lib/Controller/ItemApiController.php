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

use OCA\News\Service\ItemService;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;

class ItemApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    private $oldItemService;
    private $itemService;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        ItemService $oldItemService,
        ItemServiceV2 $itemService
    ) {
        parent::__construct($request, $userSession);

        $this->oldItemService = $oldItemService;
        $this->itemService = $itemService;
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
        $items = $this->oldItemService->findAllItems(
            $id,
            $type,
            $batchSize,
            $offset,
            $getRead,
            $oldestFirst,
            $this->getUserId()
        );

        return ['items' => $this->serialize($items)];
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
    public function updated(int $type = 3, int $id = 0, int $lastModified = 0)
    {
        // needs to be turned into a millisecond timestamp to work properly
        if (strlen((string) $lastModified) <= 10) {
            $paddedLastModified = $lastModified . '000000';
        } else {
            $paddedLastModified = $lastModified;
        }
        $items = $this->oldItemService->findAllNew(
            $id,
            $type,
            $paddedLastModified,
            true,
            $this->getUserId()
        );

        return ['items' => $this->serialize($items)];
    }


    private function setRead(bool $isRead, int $itemId)
    {
        try {
            $this->oldItemService->read($itemId, $isRead, $this->getUserId());
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


    private function setStarred(bool $isStarred, int $feedId, string $guidHash)
    {
        try {
            $this->oldItemService->star(
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
     * @param int $newestItemId
     */
    public function readAll(int $newestItemId)
    {
        $this->oldItemService->readAll($newestItemId, $this->getUserId());
    }


    private function setMultipleRead(bool $isRead, array $items)
    {
        foreach ($items as $id) {
            try {
                $this->oldItemService->read($id, $isRead, $this->getUserId());
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


    /**
     * @param bool  $isStarred
     * @param array $items
     */
    private function setMultipleStarred(bool $isStarred, array $items)
    {
        foreach ($items as $item) {
            try {
                $this->oldItemService->star(
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
     * @param array $items item ids
     */
    public function unstarMultiple(array $items)
    {
        $this->setMultipleStarred(false, $items);
    }
}
