<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Controller;

use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\FeedServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IConfig;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceException;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\ItemServiceV2;
use \OCA\News\Service\ShareService;
use OCP\IUserSession;

/**
 * Class ItemController
 *
 * @package OCA\News\Controller
 */
class ItemController extends Controller
{
    use JSONHttpErrorTrait;

    /**
     * @var ItemServiceV2
     */
    private $itemService;
    /**
     * @var FeedServiceV2
     */
    private $feedService;
    /**
     * @var ShareService
     */
    private $shareService;
    /**
     * @var IConfig
     */
    private $settings;

    public function __construct(
        IRequest $request,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        ShareService $shareService,
        IConfig $settings,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
        $this->itemService = $itemService;
        $this->feedService = $feedService;
        $this->shareService = $shareService;
        $this->settings = $settings;
    }


    /**
     * @NoAdminRequired
     *
     * @param int    $type
     * @param int    $id
     * @param int    $limit
     * @param int    $offset
     * @param bool   $showAll
     * @param bool   $oldestFirst
     * @param string $search
     * @return array
     */
    public function index(
        int $type = 3,
        int $id = 0,
        int $limit = 50,
        int $offset = 0,
        ?bool $showAll = null,
        ?bool $oldestFirst = null,
        string $search = ''
    ): array {

        // in case this is called directly and not from the website use the
        // internal state
        if ($showAll === null) {
            $showAll = $this->settings->getUserValue(
                $this->getUserId(),
                $this->appName,
                'showAll'
            ) === '1';
        }

        if ($oldestFirst === null) {
            $oldestFirst = $this->settings->getUserValue(
                $this->getUserId(),
                $this->appName,
                'oldestFirst'
            ) === '1';
        }

        $this->settings->setUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId',
            $id
        );
        $this->settings->setUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedType',
            $type
        );

        $return = [];

        // split search parameter on url space
        $search_string = trim(urldecode($search));
        $search_string = preg_replace('/\s+/', ' ', $search_string);  // remove multiple ws
        $search_items = [];
        if ($search !== '') {
            $search_items = explode(' ', $search_string);
        }

        try {
            // the offset is 0 if the user clicks on a new feed
            // we need to pass the newest feeds to not let the unread count get
            // out of sync
            if ($offset === 0) {
                $return['newestItemId'] = $this->itemService->newest($this->getUserId())->getId();
                $return['feeds'] = $this->feedService->findAllForUser($this->getUserId());
                $return['starred'] = count($this->itemService->starred($this->getUserId()));
            }

            switch ($type) {
                case ListType::FEED:
                    $items = $this->itemService->findAllInFeedWithFilters(
                        $this->getUserId(),
                        $id,
                        $limit,
                        $offset,
                        !$showAll,
                        $oldestFirst,
                        $search_items
                    );
                    break;
                case ListType::FOLDER:
                    $items = $this->itemService->findAllInFolderWithFilters(
                        $this->getUserId(),
                        $id,
                        $limit,
                        $offset,
                        !$showAll,
                        $oldestFirst,
                        $search_items
                    );
                    break;
                default:
                    $items = $this->itemService->findAllWithFilters(
                        $this->getUserId(),
                        $type,
                        $limit,
                        $offset,
                        $oldestFirst,
                        $search_items
                    );
                    break;
            }
            // Map sharer display names onto shared items
            $return['items'] = $this->shareService->mapSharedByDisplayNames($items);

            // this gets thrown if there are no items
            // in that case just return an empty response
        } catch (ServiceException $ex) {
            return [
                'items' => [],
                'feeds' => [],
                'newestItemId' => null,
                'starred' => 0,
            ];
        }

        return $return;
    }


    /**
     * @NoAdminRequired
     *
     * @param int $type
     * @param int $id
     * @param int $lastModified
     * @return array
     */
    public function newItems(int $type, int $id, $lastModified = 0): array
    {
        $showAll = $this->settings->getUserValue(
            $this->getUserId(),
            $this->appName,
            'showAll'
        ) === '1';

        $return = [];

        try {
            switch ($type) {
                case ListType::FEED:
                    $items = $this->itemService->findAllInFeedAfter(
                        $this->getUserId(),
                        $id,
                        $lastModified,
                        !$showAll
                    );
                    break;
                case ListType::FOLDER:
                    $items = $this->itemService->findAllInFolderAfter(
                        $this->getUserId(),
                        $id,
                        $lastModified,
                        !$showAll
                    );
                    break;
                default:
                    $items = $this->itemService->findAllAfter(
                        $this->getUserId(),
                        $type,
                        $lastModified
                    );
                    break;
            }

            $return['newestItemId'] = $this->itemService->newest($this->getUserId())->getId();
            $return['feeds'] = $this->feedService->findAllForUser($this->getUserId());
            $return['starred'] = count($this->itemService->starred($this->getUserId()));
            // Map sharer display names onto shared items
            $return['items'] = $this->shareService->mapSharedByDisplayNames($items);

            // this gets thrown if there are no items
            // in that case just return an empty array
        } catch (ServiceException $ex) {
            //NO-OP
        }

        return $return;
    }


    /**
     * @NoAdminRequired
     *
     * @param int    $feedId
     * @param string $guidHash
     * @param bool   $isStarred
     *
     * @return array|JSONResponse
     */
    public function star(int $feedId, string $guidHash, bool $isStarred)
    {
        try {
            $this->itemService->starByGuid(
                $this->getUserId(),
                $feedId,
                $guidHash,
                $isStarred
            );
        } catch (ServiceException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param int  $itemId
     * @param bool $isRead
     *
     * @return array|JSONResponse
     */
    public function read(int $itemId, $isRead = true)
    {
        try {
            $this->itemService->read($this->getUserId(), $itemId, $isRead);
        } catch (ServiceException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param int $highestItemId
     *
     * @return array
     */
    public function readAll(int $highestItemId): array
    {
        $this->itemService->readAll($this->getUserId(), $highestItemId);
        return ['feeds' => $this->feedService->findAllForUser($this->getUserId())];
    }


    /**
     * @NoAdminRequired
     *
     * @param int[] $itemIds item ids
     *
     * @return void
     */
    public function readMultiple(array $itemIds): void
    {
        foreach ($itemIds as $id) {
            try {
                $this->itemService->read($this->getUserId(), $id, true);
            } catch (ServiceNotFoundException | ServiceConflictException $ex) {
                continue;
            }
        }
    }


    /**
     * @NoAdminRequired
     *
     * @param int $itemId              Item to share
     * @param string $shareRecipientId User to share the item with
     */
    public function share(int $itemId, string $shareRecipientId)
    {
        try {
            $this->shareService->shareItemWithUser(
                $this->getUserId(),
                $itemId,
                $shareRecipientId
            );
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }
}
