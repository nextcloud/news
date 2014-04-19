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
 * Prompts the user to download the a file
 */
class DownloadResponse extends  Response {

	private $filename;
	private $contentType;

	/**
	 * Creates a response that prompts the user to download the file
	 * @param string $filename the name that the downloaded file should have
	 * @param string $contentType the mimetype that the downloaded file should have
	 */
	public function __construct($filename, $contentType) {
		$this->filename = $filename;
		$this->contentType = $contentType;

		$this->addHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
		$this->addHeader('Content-Type', $contentType);
	}


}
