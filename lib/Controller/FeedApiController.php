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

use Exception;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Psr7\uri_for;

class FeedApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * TODO: Remove
     * @var ItemService
     */
    private $oldItemService;

    /**
     * @var FeedServiceV2
     */
    private $feedService;

    /**
     * TODO: Remove
     * @var FeedService
     */
    private $oldFeedService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityApiSerializer
     */
    private $serializer;

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        FeedService $oldFeedService,
        FeedServiceV2 $feedService,
        ItemService $oldItemService,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->feedService = $feedService;
        $this->oldFeedService = $oldFeedService;
        $this->oldItemService = $oldItemService;
        $this->logger = $logger;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index(): array
    {

        $result = [
            'starredCount' => $this->oldItemService->starredCount($this->getUserId()),
            'feeds' => $this->serialize($this->feedService->findAllForUser($this->getUserId()))
        ];

        try {
            $result['newestItemId'] = $this->oldItemService->getNewestItemId($this->getUserId());
        } catch (ServiceNotFoundException $ex) {
            // in case there are no items, ignore
        }

        return $result;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param string   $url
     * @param int|null $folderId
     *
     * @return array|mixed|JSONResponse
     */
    public function create(string $url, ?int $folderId = null)
    {
        if ($folderId === 0) {
            $folderId = null;
        }

        try {
            $this->feedService->purgeDeleted($this->getUserId(), time() - 600);

            $feed = $this->feedService->create($this->getUserId(), $url, $folderId);
            $result = ['feeds' => $this->serialize($feed)];

            try {
                $result['newestItemId'] = $this->oldItemService->getNewestItemId($this->getUserId());
            } catch (ServiceNotFoundException $ex) {
                // in case there are no items, ignore
            }

            return $result;
        } catch (ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $feedId
     *
     * @return array|JSONResponse
     */
    public function delete(int $feedId)
    {
        try {
            $this->feedService->delete($this->getUserId(), $feedId);
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
     * @param int $feedId
     * @param int $newestItemId
     */
    public function read(int $feedId, int $newestItemId): void
    {
        $this->oldItemService->readFeed($feedId, $newestItemId, $this->getUserId());
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int      $feedId
     * @param int|null $folderId
     *
     * @return array|JSONResponse
     */
    public function move(int $feedId, ?int $folderId)
    {
        if ($folderId === 0) {
            $folderId = null;
        }

        try {
            $this->oldFeedService->patch(
                $feedId,
                $this->getUserId(),
                ['folderId' => $folderId]
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
     * @param string $feedTitle
     *
     * @return array|JSONResponse
     */
    public function rename(int $feedId, string $feedTitle)
    {
        try {
            $this->oldFeedService->patch(
                $feedId,
                $this->getUserId(),
                ['title' => $feedTitle]
            );
        } catch (ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function fromAllUsers(): array
    {
        $feeds = $this->feedService->findAll();
        $result = ['feeds' => []];

        foreach ($feeds as $feed) {
            $result['feeds'][] = [
                'id' => $feed->getId(),
                'userId' => $feed->getUserId()
            ];
        }

        return $result;
    }


    /**
     * @NoCSRFRequired
     *
     * @param string $userId
     * @param int    $feedId
     */
    public function update(string $userId, int $feedId): void
    {
        try {
            $feed = $this->feedService->find($userId, $feedId);
            $this->feedService->fetch($feed);
            // ignore update failure
        } catch (Exception $ex) {
            $this->logger->debug('Could not update feed ' . $ex->getMessage());
        }
    }
}
