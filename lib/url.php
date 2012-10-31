<?php
/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

/**
 * Used for mapping controllers and doing security checks
 * @param Controller $controller: a new instance of the controller
 * @param string $method: the name of the controller method that should be called
 * @param bool $userLoggedIn: if false, there wont be a logged in check
 * @param bool $csrfCheck: if false, there wont be a csrf check
 */
function url($controller, $method, $userLoggedInCheck=true, $csrfCheck=true){
    
	\OCP\App::setActiveNavigationEntry('news');

    if(!\OC_App::isEnabled('news')){
        \OCP\Util::writeLog('news', 'App news is not enabled!', \OCP\Util::ERROR);
        exit();
    }

    if($userLoggedInCheck){
        if(!\OC_User::isLoggedIn()){
            \OCP\Util::writeLog('news', 'User is not logged in!', \OCP\Util::ERROR);
            exit();
        }
    } 
    echo "yodd";

    if($csrfCheck){
        if(!\OC_Util::isCallRegistered()){
            \OCP\Util::writeLog('news', 'CSRF check failed', \OCP\Util::ERROR);
            //exit();
        }
    }

    $controller->$method(new Request());
}



/**
 * This class is used to wrap $_GET and $_POST to improve testability of apps
 */
class Request {
    public $get;
    public $post;
    public $user = null;

    private $userId;

    /**
     * All parameters default to the built in $_GET, $_POST and \OCP\USER::getUser()
     * @param array $get: an array with all get variables
     * @param array $post: an array with all post variables
     * @param string $userId: the id fo the user
     */
    public function __construct($get=null, $post=null, $userId=null){
        if($get === null){
            $get = $_GET;
        }

        if($post === null){
            $post = $_POST;
        }

        if($userId === null){
            $userId = \OCP\USER::getUser();
        }

        $this->get = $get;
        $this->post = $post;
        $this->userId = $userId;
    }


    /**
     * This is used to do lazy fetching for user data
     */
    public function __get($name){
        if($name === 'user' && $this->user === null){
            // FIXME: get a new user instance
        }
        return $this->$name;
    }

}