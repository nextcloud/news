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

    private $userSession;
    private $rootFolder;

    public function __construct($appName,
        IRequest $request,
        IUserSession $userSession,
        IRootFolder $rootFolder
    ) {
        parent::__construct($appName, $request, $userSession);
        $this->rootFolder = $rootFolder;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index() 
    {
        $user = $this->getUser();

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
