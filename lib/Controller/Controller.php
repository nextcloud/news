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

use OCA\News\AppInfo\Application;
use OCA\News\Controller\Exceptions\NotLoggedInException;
use OCP\AppFramework\Controller as BaseController;
use \OCP\IUser;
use \OCP\IRequest;
use \OCP\IUserSession;

/**
 * Class ApiController
 *
 * @package OCA\News\Controller
 */
class Controller extends BaseController
{

    /**
     * ApiController constructor.
     *
     * Stores the user session to be able to leverage the user in further methods
     *
     * @param IRequest          $request        The request
     * @param IUserSession|null $userSession    The user session
     */
    public function __construct(IRequest $request, private ?IUserSession $userSession)
    {
        parent::__construct(Application::NAME, $request);
    }

    /**
     * @return IUser|null
     */
    protected function getUser(): ?IUser
    {
        if ($this->userSession === null) {
            throw new NotLoggedInException();
        }

        return $this->userSession->getUser();
    }

    /**
     * @return string
     */
    protected function getUserId(): string
    {
        return $this->getUser()->getUID();
    }
}
