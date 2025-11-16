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

use OCA\News\Service\ExportService;
use OCA\News\Service\OpmlService;
use OCP\AppFramework\Http\DataDownloadResponse;
use \OCP\IRequest;
use \OCP\AppFramework\Http\JSONResponse;
use OCP\IUserSession;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\CORS;

/**
 * Class ExportController
 *
 * @package OCA\News\Controller
 */
class ExportController extends Controller
{

    public function __construct(
        IRequest $request,
        private ExportService $exportService,
        private OpmlService $opmlService,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
    }


    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function opml(): DataDownloadResponse
    {
        $date = date('Y-m-d');

        return new DataDownloadResponse(
            $this->opmlService->export($this->getUserId()),
            "subscriptions-{$date}.opml",
            'text/xml'
        );
    }

    #[NoAdminRequired]
    public function articles(): JSONResponse
    {
        $articles = $this->exportService->articles($this->getUserId());
        $response = new JSONResponse($articles);
        $response->addHeader(
            'Content-Disposition',
            'attachment; filename="articles.json"'
        );
        return $response;
    }
}
