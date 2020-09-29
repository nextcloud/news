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
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\News\Service\ItemService;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;
use OCA\News\Db\FeedType;

class FeedController extends Controller
{
    use JSONHttpErrorTrait;

    private $feedService;
    private $folderService;
    private $itemService;
    private $userId;
    private $settings;

    public function __construct(
        string $appName,
        IRequest $request,
        FolderService $folderService,
        FeedService $feedService,
        ItemService $itemService,
        IConfig $settings,
        string $UserId
    ) {
        parent::__construct($appName, $request);
        $this->feedService = $feedService;
        $this->folderService = $folderService;
        $this->itemService = $itemService;
        $this->userId = $UserId;
        $this->settings = $settings;
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
            'feeds' => $this->feedService->findAllForUser($this->userId),
            'starred' => $this->itemService->starredCount($this->userId)
        ];

        try {
            $params['newestItemId'] =
                $this->itemService->getNewestItemId($this->userId);

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
            $this->userId,
            $this->appName,
            'lastViewedFeedId'
        );
        $feedType = $this->settings->getUserValue(
            $this->userId,
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
                $this->folderService->find($this->userId, $feedId);
            } elseif ($feedType === FeedType::FEED) {
                $this->feedService->find($this->userId, $feedId);

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
     * @param int    $parentFolderId
     * @param string $title
     * @param string $user
     * @param string $password
     *
     * @return array|JSONResponse
     */
    public function create(
        string $url,
        int $parentFolderId,
        ?string $title = null,
        ?string $user = null,
        ?string $password = null
    ) {
        try {
            // we need to purge deleted feeds if a feed is created to
            // prevent already exists exceptions
            $this->feedService->purgeDeleted($this->userId, false);

            $feed = $this->feedService->create(
                $url,
                $parentFolderId,
                $this->userId,
                $title,
                $user,
                $password
            );
            $params = ['feeds' => [$feed]];

            try {
                $params['newestItemId'] =
                    $this->itemService->getNewestItemId($this->userId);

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
            $this->feedService->markDeleted($feedId, $this->userId);
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
            $feed = $this->feedService->update($this->userId, $feedId);

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
        $feed = $this->feedService->importArticles($json, $this->userId);

        $params = [
            'starred' => $this->itemService->starredCount($this->userId)
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
        $this->itemService->readFeed($feedId, $highestItemId, $this->userId);

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
            $this->feedService->unmarkDeleted($feedId, $this->userId);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }

    /**
     * @NoAdminRequired
     *
     * @param int    $feedId
     * @param bool   $pinned
     * @param bool   $fullTextEnabled
     * @param int    $updateMode
     * @param int    $ordering
     * @param int    $folderId
     * @param string $title
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
            'folderId' => $folderId
        ];

        $diff = array_filter(
            $attributes,
            function ($value) {
                return $value !== null;
            }
        );

        try {
            $this->feedService->patch($feedId, $this->userId, $diff);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }
}
