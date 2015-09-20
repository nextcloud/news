<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\IUserSession;
use \OCP\IURLGenerator;
use \OCP\Files\IRootFolder;
use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http;

class UserApiController extends ApiController {

    private $userSession;
    private $rootFolder;

    public function __construct($AppName,
                                IRequest $request,
                                IUserSession $userSession,
                                IRootFolder $rootFolder){
        parent::__construct($AppName, $request);
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index() {
        $user = $this->userSession->getUser();

        // find the avatar
        $jpgAvatar = '/' . $user->getUID() . '/avatar.jpg';
        $pngAvatar = '/' . $user->getUID() . '/avatar.png';
        $avatar = null;

        if ($this->rootFolder->nodeExists($jpgAvatar)) {
            $file = $this->rootFolder->get($jpgAvatar);
            $avatar = [
                'data' => base64_encode($file->getContent()),
                'mime' =>  'image/jpeg'
            ];
        } elseif ($this->rootFolder->nodeExists($pngAvatar)) {
            $file = $this->rootFolder->get($pngAvatar);
            $avatar = [
                'data' => base64_encode($file->getContent()),
                'mime' =>  'image/png'
            ];
        }

        return [
            'userId' => $user->getUID(),
            'displayName' => $user->getDisplayName(),
            'lastLoginTimestamp' => $user->getLastLogin(),
            'avatar' => $avatar
        ];
    }

}
