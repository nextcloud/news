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

use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ImportService;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\AppFramework\Http;

use OCA\News\Db\ListType;
use OCP\IUserSession;

class FeedController extends Controller
{
    use JSONHttpErrorTrait;

    /**
     * @var FeedServiceV2
     */
    private $feedService;
    /**
     * @var ItemServiceV2
     */
    private $itemService;
    /**
     * @var FolderServiceV2
     */
    private $folderService;
    /**
     * @var ImportService
     */
    private $importService;
    /**
     * @var IConfig
     */
    private $settings;

    public function __construct(
        IRequest $request,
        FolderServiceV2 $folderService,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        ImportService $importService,
        IConfig $settings,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
        $this->folderService = $folderService;
        $this->feedService   = $feedService;
        $this->itemService   = $itemService;
        $this->importService = $importService;
        $this->settings      = $settings;
    }


    /**
     * @NoAdminRequired
     */
    public function index(): array
    {

        // this method is also used to update the interface
        // because of this we also pass the starred count and the newest
        // item id which will be used for marking feeds read
        $params = [
            'feeds' => $this->feedService->findAllForUser($this->getUserId()),
            'starred' => count($this->itemService->starred($this->getUserId()))
        ];

        try {
            $id = $this->itemService->newest($this->getUserId())->getId();

            // An exception occurs if there is a newest item. If there is none,
            // simply ignore it and do not add the newestItemId
            $params['newestItemId'] = $id;
        } catch (ServiceNotFoundException $ex) {
            //NO-OP
        }

        return $params;
    }


    /**
     * @NoAdminRequired
     */
    public function active(): array
    {
        $feedId = (int) $this->settings->getUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedId'
        );
        $feedType = $this->settings->getUserValue(
            $this->getUserId(),
            $this->appName,
            'lastViewedFeedType'
        );

        // check if feed or folder exists
        try {
            if ($feedType === null) {
                throw new ServiceNotFoundException('First launch');
            }

            $feedType = intval($feedType);
            switch ($feedType) {
                case ListType::FOLDER:
                    $this->folderService->find($this->getUserId(), $feedId);
                    break;
                case ListType::FEED:
                    $this->feedService->find($this->getUserId(), $feedId);
                    break;
                default:
                    break;
            }
        } catch (ServiceNotFoundException $ex) {
            $feedId = 0;
            $feedType = ListType::ALL_ITEMS;
        }

        return [
            'activeFeed' => [
                'id' => $feedId,
                'type' => $feedType
            ]
        ];
    }


    /**
     * @NoAdminRequired
     *
     * @param string $url
     * @param int|null    $parentFolderId
     * @param string|null $title
     * @param string|null $user
     * @param string|null $password
     *
     * @return array|JSONResponse
     */
    public function create(
        string $url,
        ?int $parentFolderId,
        ?string $title = null,
        ?string $user = null,
        ?string $password = null,
        bool $fullDiscover = true
    ) {
        if ($parentFolderId === 0) {
            $parentFolderId = null;
        }
        try {
            // we need to purge deleted feeds if a feed is created to
            // prevent already exists exceptions
            $this->feedService->purgeDeleted($this->getUserId(), false);

            $feed = $this->feedService->create(
                $this->getUserId(),
                $url,
                $parentFolderId,
                false,
                $title,
                $user,
                $password,
                $fullDiscover
            );
            $params = ['feeds' => [$feed]];

            $this->feedService->fetch($feed);

            try {
                $id = $this->itemService->newest($this->getUserId())->getId();
                // An exception occurs if there is a newest item. If there is none,
                // simply ignore it and do not add the newestItemId
                $params['newestItemId'] = $id;
            } catch (ServiceNotFoundException $ex) {
                //NO-OP
            }

            return $params;
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     *
     * @return array|JSONResponse
     */
    public function delete(int $feedId)
    {
        try {
            $feed = $this->feedService->find($this->getUserId(), $feedId);
            $feed->setDeletedAt(time());
            $this->feedService->update($this->getUserId(), $feed);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     *
     * @return array|JSONResponse
     */
    public function update(int $feedId)
    {
        try {
            $old_feed = $this->feedService->find($this->getUserId(), $feedId);
            $feed     = $this->feedService->fetch($old_feed);

            return [
                'feeds' => [
                    // only pass unread count to not accidentally read
                    // the feed again
                    [
                        'id' => $feed->getId(),
                        'unreadCount' => $feed->getUnreadCount()
                    ]
                ]
            ];
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }


    /**
     * @NoAdminRequired
     *
     * @param array $json
     * @return array
     */
    public function import(array $json): array
    {
        $feed = $this->importService->importArticles($this->getUserId(), $json);

        $params = [
            'starred' => count($this->itemService->starred($this->getUserId()))
        ];

        if (!is_null($feed)) {
            $params['feeds'] = [$feed];
        }

        return $params;
    }


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     * @param int $highestItemId
     * @return array
     */
    public function read(int $feedId, int $highestItemId): array
    {
        $this->feedService->read($this->getUserId(), $feedId, $highestItemId);

        return [
            'feeds' => [
                [
                    'id' => $feedId,
                    'unreadCount' => 0
                ]
            ]
        ];
    }


    /**
     * @NoAdminRequired
     *
     * @param int $feedId
     *
     * @return array|JSONResponse
     */
    public function restore(int $feedId)
    {
        try {
            $feed = $this->feedService->find($this->getUserId(), $feedId);
            $feed->setDeletedAt(null);
            $this->feedService->update($this->getUserId(), $feed);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }

    /**
     * @NoAdminRequired
     *
     * @param int         $feedId
     * @param bool        $pinned
     * @param bool        $fullTextEnabled
     * @param int|null    $updateMode
     * @param int|null    $ordering
     * @param int|null    $folderId
     * @param string|null $title
     *
     * @return array|JSONResponse
     */
    public function patch(
        int $feedId,
        ?bool $pinned = null,
        ?bool $fullTextEnabled = null,
        ?int $updateMode = null,
        ?int $ordering = null,
        ?int $folderId = -1,
        ?string $title = null
    ) {
        try {
            $feed = $this->feedService->find($this->getUserId(), $feedId);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        if ($folderId !== -1) {
            $fId = $folderId === 0 ? null : $folderId;
            $feed->setFolderId($fId);
        }
        if ($pinned !== null) {
            $feed->setPinned($pinned);
        }
        if ($fullTextEnabled !== null) {
            $feed->setFullTextEnabled($fullTextEnabled);
        }
        if ($updateMode !== null) {
            $feed->setUpdateMode($updateMode);
        }
        if ($ordering !== null) {
            $feed->setOrdering($ordering);
        }
        if ($title !== null) {
            $feed->setTitle($title);
        }

        try {
            $this->feedService->update($this->getUserId(), $feed);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }
}
