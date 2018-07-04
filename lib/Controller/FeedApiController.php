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

use \OCP\IRequest;
use \OCP\ILogger;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;
use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Service\ServiceConflictException;


class FeedApiController extends ApiController
{

    use JSONHttpError;

    private $itemService;
    private $feedService;
    private $logger;
    private $loggerParams;
    private $serializer;

    public function __construct($appName,
        IRequest $request,
        IUserSession $userSession,
        FeedService $feedService,
        ItemService $itemService,
        ILogger $logger,
        $LoggerParameters
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->feedService = $feedService;
        $this->itemService = $itemService;
        $this->logger = $logger;
        $this->loggerParams = $LoggerParameters;
        $this->serializer = new EntityApiSerializer('feeds');
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index() 
    {

        $result = [
            'starredCount' => $this->itemService->starredCount($this->getUserId()),
            'feeds' => $this->feedService->findAll($this->getUserId())
        ];


        try {
            $result['newestItemId'] =
                $this->itemService->getNewestItemId($this->getUserId());

            // in case there are no items, ignore
        } catch(ServiceNotFoundException $ex) {
        }

        return $this->serializer->serialize($result);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param string $url
     * @param int    $folderId
     * @return array|mixed|\OCP\AppFramework\Http\JSONResponse
     */
    public function create($url, $folderId=0) 
    {
        try {
            $this->feedService->purgeDeleted($this->getUserId(), false);

            $feed = $this->feedService->create($url, $folderId, $this->getUserId());
            $result = ['feeds' => [$feed]];

            try {
                $result['newestItemId'] =
                    $this->itemService->getNewestItemId($this->getUserId());

                // in case there are no items, ignore
            } catch(ServiceNotFoundException $ex) {
            }

            return $this->serializer->serialize($result);

        } catch(ServiceConflictException $ex) {
            return $this->error($ex, Http::STATUS_CONFLICT);
        } catch(ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $feedId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function delete($feedId) 
    {
        try {
            $this->feedService->delete($feedId, $this->getUserId());
        } catch(ServiceNotFoundException $ex) {
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
    public function read($feedId, $newestItemId) 
    {
        $this->itemService->readFeed($feedId, $newestItemId, $this->getUserId());
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $feedId
     * @param int $folderId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function move($feedId, $folderId) 
    {
        try {
            $this->feedService->patch(
                $feedId, $this->getUserId(), ['folderId' => $folderId]
            );
        } catch(ServiceNotFoundException $ex) {
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
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function rename($feedId, $feedTitle) 
    {
        try {
            $this->feedService->patch(
                $feedId, $this->getUserId(), ['title' => $feedTitle]
            );
        } catch(ServiceNotFoundException $ex) {
            return $this->error($ex, Http::STATUS_NOT_FOUND);
        }

        return [];
    }


    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function fromAllUsers() 
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
    public function update($userId, $feedId) 
    {
        try {
            $this->feedService->update($feedId, $userId);
            // ignore update failure
        } catch(\Exception $ex) {
            $this->logger->debug(
                'Could not update feed ' . $ex->getMessage(),
                $this->loggerParams
            );
        }
    }


}
