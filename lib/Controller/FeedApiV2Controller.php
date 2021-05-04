<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Controller;

use OCA\News\Db\Feed;
use \Psr\Log\LoggerInterface;

use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\AppFramework\Http;

use \OCA\News\Service\FeedServiceV2;
use \OCA\News\Service\ItemServiceV2;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;

class FeedApiV2Controller extends ApiController
{
    use ApiPayloadTrait;
    use JSONHttpErrorTrait;

    private $itemService;
    private $feedService;
    private $loggerInterface;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        FeedServiceV2 $feedService,
        ItemServiceV2 $itemService,
        LoggerInterface $loggerInterface
    ) {
        parent::__construct($request, $userSession);
        $this->feedService = $feedService;
        $this->itemService = $itemService;

        $this->loggerInterface = $loggerInterface;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param string $url
     * @param string $name
     * @param int    $ordering
     * @param int    $folderId
     * @param bool   $isPinned
     * @param bool   $fullTextEnabled
     * @param string $basicAuthUser
     * @param string $basicAuthPassword
     * @return array|mixed|\OCP\AppFramework\Http\JSONResponse
     */
    public function create(
        string $url,
        string $name = '',
        int $ordering = 0,
        int $folderId = null,
        bool $isPinned = false,
        bool $fullTextEnabled = false,
        string $basicAuthUser = null,
        string $basicAuthPassword = null
    ) {
    }

    /**
     * @NoCSRFRequired
     *
     * @param Entity $userId
     * @param int    $feedId
     */
    public function update(
        int $feedId,
        string $url = '',
        string $name = '',
        int $ordering = 0,
        int $folderId = null,
        bool $isPinned = false,
        bool $fullTextEnabled = false,
        string $basicAuthUser = null,
        string $basicAuthPassword = null
    ) {
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @param int $feedId
     * @return array|\OCP\AppFramework\Http\JSONResponse
     */
    public function delete(int $feedId)
    {
    }

}

