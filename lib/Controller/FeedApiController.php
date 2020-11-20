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
use OCA\News\Db\Feed;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use Psr\Log\LoggerInterface;

class FeedApiController extends ApiController
{
    use JSONHttpErrorTrait, ApiPayloadTrait;

    /**
     * @var ItemServiceV2
     */
    private $itemService;

    /**
     * @var FeedServiceV2
     */
    private $feedService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        LoggerInterface $logger
    ) {
        parent::__construct($request, $userSession);
        $this->feedService = $feedService;
        $this->itemService = $itemService;
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
            'starredCount' => count($this->itemService->starred($this->getUserId())),
            'feeds' => $this->serialize($this->feedService->findAllForUser($this->getUserId()))
        ];

        try {
            $result['newestItemId'] = $this->itemService->newest($this->getUserId())->getId();
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
        $folderId = $folderId === 0 ? null : $folderId;

        try {
            $this->feedService->purgeDeleted($this->getUserId(), time() - 600);

            $feed = $this->feedService->create($this->getUserId(), $url, $folderId);
            $result = ['feeds' => $this->serialize($feed)];

            $this->feedService->fetch($feed);

            try {
                $result['newestItemId'] = $this->itemService->newest($this->getUserId())->getId();
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
        $this->itemService->read($this->getUserId(), $feedId, $newestItemId);
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
        $folderId = $folderId === 0 ? null : $folderId;

        try {
            /** @var Feed $feed */
            $feed = $this->feedService->find($this->getUserId(), $feedId);
            $feed->setFolderId($folderId);
            $this->feedService->update($this->getUserId(), $feed);
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
            /** @var Feed $feed */
            $feed = $this->feedService->find($this->getUserId(), $feedId);
            $feed->setTitle($feedTitle);
            $this->feedService->update($this->getUserId(), $feed);
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
