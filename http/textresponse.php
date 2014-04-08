<?php

/**
 * ownCloud - App Framework
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
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
