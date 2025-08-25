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

use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\CORS;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;

/**
 * Class ItemApiController
 *
 * @package OCA\News\Controller
 */
class ItemApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        private ItemServiceV2 $itemService
    ) {
        parent::__construct($request, $userSession);
    }


    /**
     * @param int  $type
     * @param int  $id
     * @param bool $getRead
     * @param int  $batchSize
     * @param int  $offset
     * @param bool $oldestFirst
     * @return array|JSONResponse
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function index(
        int $type = 3,
        int $id = 0,
        bool $getRead = true,
        int $batchSize = -1,
        int $offset = 0,
        $oldestFirst = false
    ): array {
        $oldestFirst = filter_var($oldestFirst, FILTER_VALIDATE_BOOLEAN);

        switch ($type) {
            case ListType::FEED:
                $items = $this->itemService->findAllInFeedWithFilters(
                    $this->getUserId(),
                    $id,
                    $batchSize,
                    $offset,
                    !$getRead,
                    $oldestFirst
                );
                break;
            case ListType::FOLDER:
                $items = $this->itemService->findAllInFolderWithFilters(
                    $this->getUserId(),
                    $id,
                    $batchSize,
                    $offset,
                    !$getRead,
                    $oldestFirst
                );
                break;
            default:
                // Fallback in case people try getRead here
                if ($getRead === false && $type === ListType::ALL_ITEMS) {
                    $type = ListType::UNREAD;
                } elseif ($getRead === false) {
                    return ['message' => 'Setting getRead on an already filtered list is not allowed!'];
                }

                $items = $this->itemService->findAllWithFilters(
                    $this->getUserId(),
                    $type,
                    $batchSize,
                    $offset,
                    $oldestFirst
                );
                break;
        }

        return ['items' => $this->serialize($items)];
    }


    /**
     * @param int $type
     * @param int $id
     * @param int $lastModified
     * @return array|JSONResponse
     *
     * @throws ServiceValidationException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function updated(int $type = 3, int $id = 0, int $lastModified = 0): array
    {
        // needs to be turned into a millisecond timestamp to work properly
        if (strlen((string) $lastModified) <= 10) {
            $paddedLastModified = $lastModified * 1000000;
        } else {
            $paddedLastModified = $lastModified;
        }

        switch ($type) {
            case ListType::FEED:
                $items = $this->itemService->findAllInFeedAfter($this->getUserId(), $id, $paddedLastModified, false);
                break;
            case ListType::FOLDER:
                $items = $this->itemService->findAllInFolderAfter($this->getUserId(), $id, $paddedLastModified, false);
                break;
            default:
                $items = $this->itemService->findAllAfter($this->getUserId(), $type, $paddedLastModified);
                break;
        }

        return ['items' => $this->serialize($items)];
    }

    /**
     * @param int  $itemId
     * @param bool $isRead
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    private function setRead(int $itemId, bool $isRead)
    {
        try {
            $this->itemService->read($this->getUserId(), $itemId, $isRead);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function read(int $itemId)
    {
        return $this->setRead($itemId, true);
    }


    /**
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unread(int $itemId)
    {
        return $this->setRead($itemId, false);
    }

    /**
     * @param int    $feedId
     * @param string $guidHash
     * @param bool   $isStarred
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    private function setStarred(int $feedId, string $guidHash, bool $isStarred)
    {
        try {
            $this->itemService->starByGuid($this->getUserId(), $feedId, $guidHash, $isStarred);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @param int  $itemId
     * @param bool $isStarred
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    private function setStarredByItemId(int $itemId, bool $isStarred)
    {
        try {
            $this->itemService->star($this->getUserId(), $itemId, $isStarred);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function star(int $feedId, string $guidHash)
    {
        return $this->setStarred($feedId, $guidHash, true);
    }


    /**
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unstar(int $feedId, string $guidHash)
    {
        return $this->setStarred($feedId, $guidHash, false);
    }


    /**
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function starByItemId(int $itemId)
    {
        return $this->setStarredByItemId($itemId, true);
    }


    /**
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unstarByItemId(int $itemId)
    {
        return $this->setStarredByItemId($itemId, false);
    }


    /**
     * @param int $newestItemId
     *
     * @return void
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function readAll(int $newestItemId): void
    {
        $this->itemService->readAll($this->getUserId(), $newestItemId);
    }

    /**
     * @param array $itemIds
     * @param bool  $isRead
     *
     * @throws ServiceConflictException
     */
    private function setMultipleRead(array $itemIds, bool $isRead): void
    {
        foreach ($itemIds as $id) {
            try {
                $this->itemService->read($this->getUserId(), $id, $isRead);
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }


    /**
     * @param int[] $items item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function readMultiple(array $items): void
    {
        $this->setMultipleRead($items, true);
    }


    /**
     * @param int[] $itemIds item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function readMultipleByIds(array $itemIds): void
    {
        $this->setMultipleRead($itemIds, true);
    }


    /**
     * @param int[] $items item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unreadMultiple(array $items): void
    {
        $this->setMultipleRead($items, false);
    }


    /**
     * @param int[] $itemIds item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unreadMultipleByIds(array $itemIds): void
    {
        $this->setMultipleRead($itemIds, false);
    }


    /**
     * @param array $items
     * @param bool  $isStarred
     *
     * @return void
     */
    private function setMultipleStarred(array $items, bool $isStarred): void
    {
        foreach ($items as $item) {
            try {
                $this->itemService->starByGuid(
                    $this->getUserId(),
                    $item['feedId'],
                    $item['guidHash'],
                    $isStarred
                );
            } catch (ServiceNotFoundException | ServiceConflictException $ex) {
                continue;
            }
        }
    }


    /**
     * @param array $itemIds
     * @param bool  $isStarred
     *
     * @return void
     */
    private function setMultipleStarredByItemIds(array $itemIds, bool $isStarred): void
    {
        foreach ($itemIds as $itemId) {
            try {
                $this->itemService->star(
                    $this->getUserId(),
                    $itemId,
                    $isStarred
                );
            } catch (ServiceNotFoundException | ServiceConflictException $ex) {
                continue;
            }
        }
    }


    /**
     * @param int[] $items items
     *
     * @return void
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function starMultiple(array $items): void
    {
        $this->setMultipleStarred($items, true);
    }


    /**
     * @param array $items items
     *
     * @return void
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unstarMultiple(array $items): void
    {
        $this->setMultipleStarred($items, false);
    }


    /**
     * @param int[] $items item ids
     *
     * @return void
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function starMultipleByItemIds(array $itemIds): void
    {
        $this->setMultipleStarredByItemIds($itemIds, true);
    }


    /**
     * @param array $items item ids
     *
     * @return void
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function unstarMultipleByItemIds(array $itemIds): void
    {
        $this->setMultipleStarredByItemIds($itemIds, false);
    }
}
