<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Controller;

use OCP\IRequest;
use OCP\IUserSession;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataDownloadResponse;

use OCA\News\Constants;
use OCA\News\Utility\AppData;

class FaviconApiController extends ApiController
{
    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        private AppData $appData
    ) {
        parent::__construct($request, $userSession);
        $this->appData = $appData;
    }

    /**
     * @param string $feedUrlHash
     *
     * @return Http\DataDownloadResponse|array
     */
    #[CORS]
    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function get(string $feedUrlHash)
    {
        $feed_logo = null;
        $logo_hash = $this->appData->getFileContent(Constants::LOGO_INFO_DIR, 'img_'.$feedUrlHash);
        if ($logo_hash) {
            $feed_logo = $this->appData->getFileContent(Constants::LOGO_IMAGE_DIR, $logo_hash);
        }
        if (!$feed_logo) {
            $feed_logo = file_get_contents(__DIR__ . '/../../img/rss.svg');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($feed_logo);
        return new DataDownloadResponse($feed_logo, $feedUrlHash, $mime);
    }
}
