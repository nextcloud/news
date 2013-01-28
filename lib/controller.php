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
	protected $appName;
	protected $request;
	protected $api;
	protected $trans;

	public function __construct($request, $api){
		$this->api = $api;
		$this->userId = $api->getUserId();
		$this->appName = $api->getAppName();
		$this->request = $request;
		$this->trans = $api->getTrans();
	}


	/**
	 * @brief lets you access post and get parameters by the index
	 * @param string $key: the key which you want to access in the $_POST or
	 *                     $_GET array. If both arrays store things under the same
	 *                     key, return the value in $_POST
	 * @param $default: the value that is returned if the key does not exist
	 * @return: the content of the array
	 */
	protected function params($key, $default=null){
		$postValue = $this->request->getPOST($key);
		$getValue = $this->request->getGET($key);
		
		if($postValue !== null){
			return $postValue;
		}

		if($getValue !== null){
			return $getValue;
		}

		return $default;
	}

	/**
	 * Shortcut for accessing an uploaded file through the $_FILES array
	 * @param string $key: the key that will be taken from the $_FILES array
	 * @return the file in the $_FILES element
	 */
	protected function getUploadedFile($key){
		return $this->request->getFILES($key);
	}


	/**
	 * Binds variables to the template and prints it
	 * The following values are always assigned: userId, trans
	 * @param $templateName the name of the template
	 * @param $arguments an array with arguments in $templateVar => $content
	 * @param string $renderAs: admin, user or blank: admin renders the page on
	 *                          the admin settings page, user renders a normal
	 *                          owncloud page, blank renders the template alone
	 */
	protected function render($templateName, $arguments=array(),
							  $renderAs='user'){
		$response = new TemplateResponse($this->appName, $templateName);
		$response->setParams($arguments);
		$response->renderAs($renderAs);
		return $response;
	}


	/**
	 * @brief renders a json success
	 * @param array $params an array which will be converted to JSON
	 */
	protected function renderJSON($params=array()){
		$response = new JSONResponse($this->appName);
		$response->setParams($params);
		return $response;
	}


	/**
	 * @brief renders a json error
	 * @param string $msg: the error message
	 * @param string $file: the file that it occured in
	 * @param array $params an array which will be converted to JSON
	 */
	protected function renderJSONError($msg, $file="", $params=array()){
		$response = new JSONResponse($this->appName);
		$response->setParams($params);
		$response->setErrorMessage($msg, $file);
		return $response;
	}


}
