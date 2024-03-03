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

use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\OpmlService;
use OCP\AppFramework\Http\DataDownloadResponse;
use \OCP\IRequest;
use \OCP\AppFramework\Http\JSONResponse;
use OCP\IUserSession;

/**
 * Class ExportController
 *
 * @package OCA\News\Controller
 */
class ExportController extends Controller
{

    private $opmlService;
    private $folderService;
    private $feedService;
    private $itemService;

    public function __construct(
        IRequest $request,
        FolderServiceV2 $folderService,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        OpmlService $opmlService,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
        $this->feedService = $feedService;
        $this->folderService = $folderService;
        $this->opmlService = $opmlService;
        $this->itemService = $itemService;
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function opml(): DataDownloadResponse
    {
        $date = date('Y-m-d');

        return new DataDownloadResponse(
            $this->opmlService->export($this->getUserId()),
            "subscriptions-{$date}.opml",
            'text/xml'
        );
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function articles(): JSONResponse
    {
        $feeds = $this->feedService->findAllForUser($this->getUserId());
        $starred = $this->itemService->findAllForUser($this->getUserId(), ['unread' => false, 'starred' => true]);
        $unread = $this->itemService->findAllForUser($this->getUserId(), ['unread' => true]);

        $items = array_merge($starred, $unread);

        // build assoc array for fast access
        $feedsDict = [];
        foreach ($feeds as $feed) {
            $feedsDict['feed' . $feed->getId()] = $feed;
        }

        $articles = [];
        foreach ($items as $item) {
            $articles[] = $item->toExport($feedsDict);
        }

        $response = new JSONResponse($articles);
        $response->addHeader(
            'Content-Disposition',
            'attachment; filename="articles.json"'
        );
        return $response;
    }
}
