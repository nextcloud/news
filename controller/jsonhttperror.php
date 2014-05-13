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

namespace OCA\News\Controller;

use \OCP\AppFramework\Http\JSONResponse;


trait JSONHttpError {


	/**
	 * @param \Excpetion $exception the message that is returned taken from the
	 * exception
	 * @param int the http error code
	 * @return \OCP\AppFramework\Http\JSONResponse
	 */
	protected function error($exception, $code) {
		return new JSONResponse(
			array('message' => $exception->getMessage()), 
			$code
		);
	}


}