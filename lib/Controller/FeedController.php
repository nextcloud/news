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
use OCA\News\Service\FolderServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\AppFramework\Http;

use OCA\News\Service\ItemService;
use OCA\News\Service\FeedService;
use OCA\News\Db\FeedType;
use OCP\IUserSession;

class FeedController extends Controller
{
    use JSONHttpErrorTrait;

    //TODO: Remove
    private $feedService;
    //TODO: Remove
    private $itemService;
    /**
     * @var FolderServiceV2
     */
    private $folderService;
    /**
     * @var IConfig
     */
    private $settings;

    public function __construct(
        string $appName,
        IRequest $request,
        FolderServiceV2 $folderService,
        FeedService $feedService,
        ItemService $itemService,
        IConfig $settings,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->folderService = $folderService;
        $this->feedService   = $feedService;
        $this->itemService   = $itemService;
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
            'starred' => $this->itemService->starredCount($this->getUserId())
        ];

        try {
            $params['newestItemId'] =
                $this->itemService->getNewestItemId($this->getUserId());

            // An exception occurs if there is a newest item. If there is none,
            // simply ignore it and do not add the newestItemId
        } catch (ServiceNotFoundException $ex) {
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

        // cast from null to int is 0
        if ($feedType !== null) {
            $feedType = (int) $feedType;
        }

        // check if feed or folder exists
        try {
            if ($feedType === FeedType::FOLDER) {
                if ($feedId === 0) {
                    $feedId = null;
                }
                $this->folderService->find($this->getUserId(), $feedId);
            } elseif ($feedType === FeedType::FEED) {
                $this->feedService->find($this->getUserId(), $feedId);

                // if its the first launch, those values will be null
            } elseif ($feedType === null) {
                throw new ServiceNotFoundException('');
            }
        } catch (ServiceNotFoundException $ex) {
            $feedId = 0;
            $feedType = FeedType::SUBSCRIPTIONS;
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
        ?string $password = null
    ) {
        if ($parentFolderId === 0) {
            $parentFolderId = null;
        }
        try {
            // we need to purge deleted feeds if a feed is created to
            // prevent already exists exceptions
            $this->feedService->purgeDeleted($this->getUserId(), false);

            $feed = $this->feedService->create(
                $url,
                $parentFolderId,
                $this->getUserId(),
                $title,
                $user,
                $password
            );
            $params = ['feeds' => [$feed]];

            try {
                $params['newestItemId'] =
                    $this->itemService->getNewestItemId($this->getUserId());

                // An exception occurs if there is a newest item. If there is none,
                // simply ignore it and do not add the newestItemId
            } catch (ServiceNotFoundException $ex) {
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
            $this->feedService->markDeleted($feedId, $this->getUserId());
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
            $feed = $this->feedService->update($this->getUserId(), $feedId);

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
        $feed = $this->feedService->importArticles($json, $this->getUserId());

        $params = [
            'starred' => $this->itemService->starredCount($this->getUserId())
        ];

        if ($feed) {
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
        $this->itemService->readFeed($feedId, $highestItemId, $this->getUserId());

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
            $this->feedService->unmarkDeleted($feedId, $this->getUserId());
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
        ?int $folderId = null,
        ?string $title = null
    ) {
        $attributes = [
            'pinned' => $pinned,
            'fullTextEnabled' => $fullTextEnabled,
            'updateMode' => $updateMode,
            'ordering' => $ordering,
            'title' => $title,
            'folderId' => $folderId === 0 ? null : $folderId
        ];

        $diff = array_filter(
            $attributes,
            function ($value) {
                return $value !== null;
            }
        );

        try {
            $this->feedService->patch($feedId, $this->getUserId(), $diff);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }
}
