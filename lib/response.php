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


abstract class Response {

	private $headers;

	protected function __construct(){
		$this->headers = array();
	}

	/**
	 * Adds a new header to the response that will be called before the render
	 * function
	 * @param string header: the string that will be used in the header() function
	 */
	public function addHeader($header){
		array_push($this->headers, $header);
	}


	/**
	 * Renders all headers
	 */
	public function render(){
		foreach($this->headers as $value) {
			header($value);
		}
	}


}


/**
 * Prompts the user to download the a textfile
 */
class TextDownloadResponse extends Response {
	
	private $content;
	private $filename;
	private $contentType;

	/**
	 * Creates a response that prompts the user to download the file
	 * @param string $content: the content that should be written into the file
	 * @param string $filename: the name that the downloaded file should have
	 * @param string $contentType: the mimetype that the downloaded file should have
	 */
	public function __construct($content, $filename, $contentType){
		parent::__construct();
		$this->content = $content;
		$this->filename = $filename;
		$this->contentType = $contentType;

		$this->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
		$this->addHeader('Content-Type: ' . $contentType);
	}


	/**
	 * Simply sets the headers and returns the file contents
	 * @return the file contents
	 */
	public function render(){
		parent::render();
		return $this->content;
	}


}


/**
 * Response for a normal template
 */
class TemplateResponse extends Response {

	private $templateName;
	private $params;
	private $appName;
	private $renderAs;

	/**
	 * @param string $appName: the name of your app
	 * @param string $templateName: the name of the template
	 */
	public function __construct($appName, $templateName) {
		parent::__construct();
		$this->templateName = $templateName;
		$this->appName = $appName;
		$this->params = array();
		$this->renderAs = 'user';
	}


	/**
	 * @brief sets template parameters
	 * @param array $params: an array with key => value structure which sets template
	 *                       variables
	 */
	public function setParams($params){
		$this->params = $params;
	}


	/**
	 * @brief sets the template page
	 * @param string $renderAs: admin, user or blank: admin renders the page on
	 *                          the admin settings page, user renders a normal
	 *                          owncloud page, blank renders the template alone
	 */
	public function renderAs($renderAs='user'){
		$this->renderAs = $renderAs;
	}


	/**
	 * Returns the rendered html
	 * @return the rendered html
	 */
	public function render(){
		parent::render();

		if($this->renderAs === 'blank'){
			$template = new \OCP\Template($this->appName, $this->templateName);
		} else {
			$template = new \OCP\Template($this->appName, $this->templateName,
											$this->renderAs);
		}

		foreach($this->params as $key => $value){
			$template->assign($key, $value, false);
		}

		return $template->fetchPage();
	}

}


/**
 * A renderer for JSON calls
 */
class JSONResponse extends Response {

	private $name;
	private $data;
	private $appName;

	/**
	 * @param string $appName: the name of your app
	 */
	public function __construct($appName) {
		parent::__construct();
		$this->appName = $appName;
		$this->data = array();
		$this->error = false;
	}

	/**
	 * @brief sets values in the data json array
	 * @param array $params: an array with key => value structure which will be
	 *                       transformed to JSON
	 */
	public function setParams($params){
		$this->data['data'] = $params;
	}


	/**
	 * @brief in case we want to render an error message, also logs into the
	 *        owncloud log
	 * @param string $message: the error message
	 * @param string $file: the file where the error occured, use __FILE__ in
	 *                      the file where you call it
	 */
	public function setErrorMessage($msg, $file){
		$this->error = true;
		$this->data['msg'] = $msg;
		\OCP\Util::writeLog($this->appName, $file . ': ' . $msg, \OCP\Util::ERROR);
	}


	/**
	 * Returns the rendered json
	 * @return the rendered json
	 */
	public function render(){
		parent::render();

		ob_start();

		if($this->error){
		\OCP\JSON::error($this->data);
		} else {
		\OCP\JSON::success($this->data);
		}

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

}