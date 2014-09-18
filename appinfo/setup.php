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

namespace OCA\News\AppInfo;

function is_setup() {
	// prevent breakage on 5.4
	if (version_compare(phpversion(), '5.4', '<')) {
		return false;
	}

	// disable useless codechecker in case security up dates are shipped but
	// blocked because of a bug in the checker
	if (\OCP\Config::getSystemValue('appcodechecker') !== false) {
		\OCP\Config::setSystemValue('appcodechecker', false);
	}

	return true;
}
