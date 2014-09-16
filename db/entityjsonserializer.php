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

namespace OCA\News\Db;

trait EntityJSONSerializer {


	public function serializeFields($properties) {
		$result = [];
		foreach($properties as $property) {
			$result[$property] = $this->$property;
		}
		return $result;
	}


}