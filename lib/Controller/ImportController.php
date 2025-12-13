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

use OCA\News\Service\ImportService;
use OCA\News\Service\OpmlService;
use OCA\News\Service\Exceptions\ServiceValidationException;
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
        private ImportService $importService,
        private OpmlService $opmlService
    ) {
        parent::__construct($request, $userSession);
    }


    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function opml(): array
    {
        $data = '';
        if (isset($this->request->files['file'])) {
            $file = $this->request->files['file'];
            $data = file_get_contents($file['tmp_name']);
        } else {
            $data = $this->request->getContent();
        }


        $status = '';
        $message = '';
        try {
            $this->opmlService->import($this->getUserId(), $data);
            $status = "ok";
        } catch (ServiceValidationException $e) {
            $status = "error";
            $message = $e->getMessage();
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return $response;
    }

    #[NoCSRFRequired]
    #[NoAdminRequired]
    public function articles(): array
    {
        $data = '';
        if (isset($this->request->files['file'])) {
            $file = $this->request->files['file'];
            $data = file_get_contents($file['tmp_name']);
        }


        $status = '';
        $message = '';

        if (!json_validate($data)) {
            return [
                'status'  => 'error',
                'message' => 'Invalid json file.',
            ];
        }

        try {
            $this->importService->articles($this->getUserId(), json_decode($data, true));
            $status = "ok";
        } catch (ServiceValidationException $e) {
            $status = "error";
            $message = $e->getMessage();
        }

        $response = [
            'status' => $status,
            'message' => $message,
        ];

        return $response;
    }
}
