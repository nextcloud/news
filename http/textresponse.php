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

namespace OCA\News\Http;

use \OCP\AppFramework\Http\Response;

/**
 * Just outputs text to the browser
 */
class TextResponse extends Response {

	private $content;

	/**
	 * Creates a response that just outputs text
	 * @param string $content the content that should be written into the file
	 * @param string $contentType the mimetype. text/ is added automatically so
	 * only plain or html can be added to get text/plain or text/html
	 */
	public function __construct($content, $contentType='plain'){
		$this->content = $content;
		$this->addHeader('Content-type', 'text/' . $contentType);
	}


	/**
	 * Simply sets the headers and returns the file contents
	 * @return string the file contents
	 */
	public function render(){
		return $this->content;
	}


}
