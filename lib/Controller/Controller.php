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
 * @copyright 2020 Sean Molenaar <sean@seanmolenaar.eu>
 */

namespace OCA\News\Controller;

use OCP\AppFramework\Controller as BaseController;
use \OCP\IRequest;
use OCP\IUser;
use \OCP\IUserSession;

/**
 * Class ApiController
 *
 * @package OCA\News\Controller
 */
class Controller extends BaseController
{
    /**
     * @var IUserSession
     */
    private $userSession;

    /**
     * ApiController constructor.
     *
     * Stores the user session to be able to leverage the user in further methods
     *
     * @param string        $appName        The name of the app
     * @param IRequest      $request        The request
     * @param IUserSession  $userSession    The user session
     */
    public function __construct(string $appName, IRequest $request, IUserSession $userSession)
    {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
    }

    /**
     * @return IUser
     */
    protected function getUser()
    {
        return $this->userSession->getUser();
    }

    /**
     * @return string
     */
    protected function getUserId()
    {
        return $this->getUser()->getUID();
    }
}
