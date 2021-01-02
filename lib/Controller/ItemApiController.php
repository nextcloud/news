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

use OCA\News\Db\FeedType;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;

/**
 * Class ItemApiController
 *
 * @package OCA\News\Controller
 */
class ItemApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * @var ItemServiceV2
     */
    private $itemService;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        ItemServiceV2 $itemService
    ) {
        parent::__construct($request, $userSession);

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
     * @return array|JSONResponse
     */
    public function index(
        int $type = 3,
        int $id = 0,
        bool $getRead = true,
        int $batchSize = -1,
        int $offset = 0,
        bool $oldestFirst = false
    ): array {
        switch ($type) {
            case FeedType::FEED:
                $items = $this->itemService->findAllInFeedWithFilters(
                    $this->getUserId(),
                    $id,
                    $batchSize,
                    $offset,
                    !$getRead,
                    $oldestFirst
                );
                break;
            case FeedType::FOLDER:
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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $type
     * @param int $id
     * @param int $lastModified
     * @return array|JSONResponse
     *
     * @throws ServiceValidationException
     */
    public function updated(int $type = 3, int $id = 0, int $lastModified = 0): array
    {
        // needs to be turned into a millisecond timestamp to work properly
        if (strlen((string) $lastModified) <= 10) {
            $paddedLastModified = $lastModified * 1000000;
        } else {
            $paddedLastModified = $lastModified;
        }

        switch ($type) {
            case FeedType::FEED:
                $items = $this->itemService->findAllInFeedAfter($this->getUserId(), $id, $paddedLastModified, false);
                break;
            case FeedType::FOLDER:
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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    public function read(int $itemId)
    {
        return $this->setRead($itemId, true);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $itemId
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int    $feedId
     * @param string $guidHash
     *
     * @return array|JSONResponse
     * @throws ServiceConflictException
     */
    public function star(int $feedId, string $guidHash)
    {
        return $this->setStarred($feedId, $guidHash, true);
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
     * @throws ServiceConflictException
     */
    public function unstar(int $feedId, string $guidHash)
    {
        return $this->setStarred($feedId, $guidHash, false);
    }


    /**
     * @NoAdminRequired
     *
     * @NoCSRFRequired
     *
     * @CORS
     *
     * @param int $newestItemId
     *
     * @return void
     */
    public function readAll(int $newestItemId): void
    {
        $this->itemService->readAll($this->getUserId(), $newestItemId);
    }

    /**
     * @param array $items
     * @param bool  $isRead
     *
     * @throws ServiceConflictException
     */
    private function setMultipleRead(array $items, bool $isRead): void
    {
        foreach ($items as $id) {
            try {
                $this->itemService->read($this->getUserId(), $id, $isRead);
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }


    /**
     * @NoAdminRequired
     *
     * @NoCSRFRequired
     *
     * @CORS
     *
     * @param int[] $items item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    public function readMultiple(array $items): void
    {
        $this->setMultipleRead($items, true);
    }


    /**
     * @NoAdminRequired
     *
     * @NoCSRFRequired
     *
     * @CORS
     *
     * @param int[] $items item ids
     *
     * @return void
     *
     * @throws ServiceConflictException
     */
    public function unreadMultiple(array $items): void
    {
        $this->setMultipleRead($items, false);
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
     * @NoAdminRequired
     *
     * @NoCSRFRequired
     *
     * @CORS
     *
     * @param int[] $items item ids
     *
     * @return void
     */
    public function starMultiple(array $items): void
    {
        $this->setMultipleStarred($items, true);
    }


    /**
     * @NoAdminRequired
     *
     * @NoCSRFRequired
     *
     * @CORS
     *
     * @param array $items item ids
     *
     * @return void
     */
    public function unstarMultiple(array $items): void
    {
        $this->setMultipleStarred($items, false);
    }
}
