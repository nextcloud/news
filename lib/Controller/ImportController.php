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

use OCA\News\Service\OpmlService;
use \OCP\IRequest;
use OCP\IUserSession;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

/**
 * Class ExportController
 *
 * @package OCA\News\Controller
 */
class ImportController extends Controller
{

    public function __construct(
        IRequest $request,
        ?IUserSession $userSession,
        private OpmlService $opmlService
    ) {
        parent::__construct($request, $userSession);
    }


    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function opml(): void
    {
        $data = '';
        if ($this->request->files->has('file')) {
            $file = $this->request->files->get('file');
            $data = $file->getContent();
        } else {
            $data = $this->request->getContent();
        }

        $this->opmlService->import($this->getUserId(), $data);
    }
}
