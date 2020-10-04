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

use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Utility\PsrLogger;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\ILogger;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Psr7\uri_for;

class FeedApiController extends ApiController
{
    use JSONHttpErrorTrait;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * @var FeedService
     */
    private $feedService;

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
        FeedService $feedService,
        ItemService $itemService,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->feedService = $feedService;
        $this->itemService = $itemService;
        $this->logger = $logger;
        $this->serializer = new EntityApiSerializer('feeds');
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index(): array
    {

        $result = [
            'starredCount' => $this->itemService->starredCount($this->getUserId()),
            'feeds' => $this->feedService->findAllForUser($this->getUserId())
        ];


        try {
            $result['newestItemId'] =
                $this->itemService->getNewestItemId($this->getUserId());

            // in case there are no items, ignore
        } catch (ServiceNotFoundException $ex) {
        }

        return $this->serializer->serialize($result);
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
            $this->feedService->purgeDeleted($this->getUserId(), false);

            $feed = $this->feedService->create($url, $folderId, $this->getUserId());
            $result = ['feeds' => [$feed]];

            try {
                $result['newestItemId'] =
                    $this->itemService->getNewestItemId($this->getUserId());

                // in case there are no items, ignore
            } catch (ServiceNotFoundException $ex) {
            }

            return $this->serializer->serialize($result);
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
            $this->feedService->delete($feedId, $this->getUserId());
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
        $this->itemService->readFeed($feedId, $newestItemId, $this->getUserId());
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
            $this->feedService->patch(
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
            $this->feedService->patch(
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
        $feeds = $this->feedService->findAllFromAllUsers();
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
            $this->feedService->update($userId, $feedId);
            // ignore update failure
        } catch (\Exception $ex) {
            $this->logger->debug('Could not update feed ' . $ex->getMessage());
        }
    }
}
