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

use \OCP\IRequest;
use \OCP\IConfig;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceException;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\FeedService;

class ItemController extends Controller
{
    use JSONHttpErrorTrait;

    private $itemService;
    private $feedService;
    private $userId;
    private $settings;

    public function __construct(
        $appName,
        IRequest $request,
        FeedService $feedService,
        ItemService $itemService,
        IConfig $settings,
        $UserId
    ) {
        parent::__construct($appName, $request);
        $this->itemService = $itemService;
        $this->feedService = $feedService;
        $this->userId = $UserId;
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
        $type = 3,
        $id = 0,
        $limit = 50,
        $offset = 0,
        $showAll = null,
        $oldestFirst = null,
        $search = ''
    ) {

        // in case this is called directly and not from the website use the
        // internal state
        if ($showAll === null) {
            $showAll = $this->settings->getUserValue(
                $this->userId,
                $this->appName,
                'showAll'
            ) === '1';
        }

        if ($oldestFirst === null) {
            $oldestFirst = $this->settings->getUserValue(
                $this->userId,
                $this->appName,
                'oldestFirst'
            ) === '1';
        }

        $this->settings->setUserValue(
            $this->userId,
            $this->appName,
            'lastViewedFeedId',
            $id
        );
        $this->settings->setUserValue(
            $this->userId,
            $this->appName,
            'lastViewedFeedType',
            $type
        );

        $params = [];

        // split search parameter on url space
        $search = trim(urldecode($search));
        $search = preg_replace('/\s+/', ' ', $search);  // remove multiple ws
        if ($search === '') {
            $search = [];
        } else {
            $search = explode(' ', $search);
        }

        try {
            // the offset is 0 if the user clicks on a new feed
            // we need to pass the newest feeds to not let the unread count get
            // out of sync
            if ($offset === 0) {
                $params['newestItemId'] =
                    $this->itemService->getNewestItemId($this->userId);
                $params['feeds'] = $this->feedService->findAll($this->userId);
                $params['starred'] =
                    $this->itemService->starredCount($this->userId);
            }

            $params['items'] = $this->itemService->findAll(
                $id,
                $type,
                $limit,
                $offset,
                $showAll,
                $oldestFirst,
                $this->userId,
                $search
            );

            // this gets thrown if there are no items
            // in that case just return an empty array
        } catch (ServiceException $ex) {
        }

        return $params;
    }


    /**
     * @NoAdminRequired
     *
     * @param int $type
     * @param int $id
     * @param int $lastModified
     * @return array
     */
    public function newItems($type, $id, $lastModified = 0)
    {
        $showAll = $this->settings->getUserValue(
            $this->userId,
            $this->appName,
            'showAll'
        ) === '1';

        $params = [];

        try {
            $params['newestItemId'] =
                $this->itemService->getNewestItemId($this->userId);
            $params['feeds'] = $this->feedService->findAll($this->userId);
            $params['starred'] =
                $this->itemService->starredCount($this->userId);
            $params['items'] = $this->itemService->findAllNew(
                $id,
                $type,
                $lastModified,
                $showAll,
                $this->userId
            );

            // this gets thrown if there are no items
            // in that case just return an empty array
        } catch (ServiceException $ex) {
        }

        return $params;
    }


    /**
     * @NoAdminRequired
     *
     * @param int    $feedId
     * @param string $guidHash
     * @param bool   $isStarred
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function star($feedId, $guidHash, $isStarred)
    {
        try {
            $this->itemService->star(
                $feedId,
                $guidHash,
                $isStarred,
                $this->userId
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
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function read($itemId, $isRead = true)
    {
        try {
            $this->itemService->read($itemId, $isRead, $this->userId);
        } catch (ServiceException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoAdminRequired
     *
     * @param int $highestItemId
     * @return array
     */
    public function readAll($highestItemId)
    {
        $this->itemService->readAll($highestItemId, $this->userId);
        return ['feeds' => $this->feedService->findAll($this->userId)];
    }


    /**
     * @NoAdminRequired
     *
     * @param int[] $itemIds item ids
     */
    public function readMultiple($itemIds)
    {
        foreach ($itemIds as $id) {
            try {
                $this->itemService->read($id, true, $this->userId);
            } catch (ServiceNotFoundException $ex) {
                continue;
            }
        }
    }
}
