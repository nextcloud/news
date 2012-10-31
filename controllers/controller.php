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


/*

Usage

MyController extends Controller {
    
    public function __construct($request=null, $userLoggedInCheck=true, $csrfCheck=true){
        super($request, $userLoggedInCheck, $csrfCheck);
    }

    public function myRoute(){
    
    }

}


*/


namespace OCA\News;

class Controller {

    protected $trans;

    public function __construct(){
        $this->trans = \OC_L10N::get('news');
        $this->safeParams = array();


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
     * Renders a renderer and sets the csrf check and logged in check to true
     * @param Renderer $renderer: the render which should be used to render the page
     */
    protected function render(Renderer $renderer){
        $renderer->bind('userId', $this->request->userId);
        $renderer->render();
        $this->csrfCheck = true;
        $this->userLoggedInCheck = true;
    }


    /**
     * Binds variables to the template and prints it
     * @param $templateName the name of the template
     * @param $arguments an array with arguments in $templateVar => $content
     * @param $safeParams template parameters which should not be escaped
     * @param $fullPage if true, it will render a full page, otherwise only a part
     *                  defaults to true
     */
    protected function renderTemplate($templateName, $arguments=array(), 
                                      $safeParams=array(), $fullPage=true){
        $renderer = new TemplateRenderer($templateName, $fullPage);
        $renderer->bindSafe($safeParams);
        $this->render($renderer);
    }

    /**
     * Binds variables to a JSON array and prints it
     * @param $arguments an array with arguments in $key => $value
     * @param $error: Empty by default. If set, a log message written and the
     *                $error will be sent to the client
     */
    protected function renderJSON($arguments=array(), $error=""){
        $renderer = new JSONRenderer($error);
        $this->render($renderer);
    }


}






interface Renderer {
    public function render();
    public function bind($params);
}



class TemplateRenderer implements Renderer {

    private $safeParams = array();

    public function __construct($name, $fullPage=true){
        if($fullPage){
            $this->template = new \OCP\Template('news', $template, 'user');
        } else {
            $this->template = new \OCP\Template('news', $template);
        }
    }

    public function bindSafe($params){
        $this->safeParams = $params;
    }


    public function bind($params){
        foreach($params as $key => $value){
            if(array_key_exists($key, $this->safeParams)) {
                $this->template->assign($key, $value, false);
            } else {
                $this->template->assign($key, $value);
            }
        }
    }


    public function render(){
        $this->template->printPage();
    }


}


class JSONRenderer implements Renderer {

    private $params;

    public function __construct($error){
        $this->error = $error;
    }


    public function bind($params){
        $this->params = $params;
    }


    public function render(){
        if($this->error === ""){
            OCP\JSON::success($this->params);        
        } else {
            OCP\JSON::error(array(
                                'data' => array('message' => $l->t('An error occured: ') . $error)
                            )
            );
            OCP\Util::writeLog('news',$_SERVER['REQUEST_URI'] . 'Error: '. $error, OCP\Util::ERROR);
            exit();
        }
        
    }

    
}