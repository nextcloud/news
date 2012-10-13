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

class Controller {

    protected $userId;
    protected $trans;

    
    public function __construct(){
        $this->userId = \OCP\USER::getUser();
        $this->trans = \OC_L10N::get('news');
    }


    protected function addScript($name){
        \OCP\Util::addScript('news', $name);
    }


    protected function addStyle($name){
        \OCP\Util::addStyle('news', $name);
    }


    protected function add3rdPartyScript($name){
        \OCP\Util::addScript('news/3rdparty', $name);
    }


    protected function add3rdPartyStyle($name){
        \OCP\Util::addStyle('news/3rdparty', $name);
    }


    /**
     * Shortcut for setting a user defined value
     * @param $key the key under which the value is being stored
     * @param $value the value that you want to store
     */
    protected function setUserValue($key, $value){
        \OCP\Config::setUserValue($this->userId, 'news', $key, $value); 
    }


    /**
     * Shortcut for getting a user defined value
     * @param $key the key under which the value is being stored
     */
    protected function getUserValue($key){
        return \OCP\Config::getUserValue($this->userId, 'news', $key);
    }


    /**
     * Binds variables to the template and prints it
     * The following values are always assigned: userId
     * @param $arguments an array with arguments in $templateVar => $content
     * @param $template the name of the template
     * @param $fullPage if true, it will render a full page, otherwise only a part
     *                  defaults to true
     */
    protected function render($template, $arguments=array(), $fullPage=true){
        
        if($fullPage){
            $template = new \OCP\Template('news', $template, 'user');
        } else {
            $template = new \OCP\Template('news', $template);
        }
        
        foreach($arguments as $key => $value){
            $template->assign($key, $value);
        }

        $template->assign('userId', $this->userId);
        $template->printPage();
    }

}

?>