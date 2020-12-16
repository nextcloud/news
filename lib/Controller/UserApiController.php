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
use \OCP\IUserSession;
use \OCP\IURLGenerator;
use \OCP\Files\IRootFolder;
use \OCP\AppFramework\Http;

class UserApiController extends ApiController
{
    public function __construct(
        IRequest $request,
        ?IUserSession $userSession
    ) {
        parent::__construct($request, $userSession);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     *
     * @deprecated Should use https://docs.nextcloud.com/server/latest/developer_manual/client_apis/OCS/ocs-api-overview.html#user-metadata
     *             and avatar is `https://nc.url/avatar/{userid}/{size}?v={1|2}`
     */
    public function index(): array
    {
        $user = $this->getUser();
        $avatar = null;

        return [
            'userId' => $user->getUID(),
            'displayName' => $user->getDisplayName(),
            'lastLoginTimestamp' => $user->getLastLogin(),
            'avatar' => $avatar
        ];
    }
}
