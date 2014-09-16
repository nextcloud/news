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


/**
 * Shortcut for adding scripts to a page
 * @param string $app the appname
 * @param string|string[] $file the filename,
 * if an array is given it will add all scripts
 */
function script($app, $file) {
	if(is_array($file)) {
		foreach($file as $f) {
			OC_Util::addScript($app, $f);
		}
	} else {
		OC_Util::addScript($app, $file);
	}
}

/**
 * Shortcut for adding styles to a page
 * @param string $app the appname
 * @param string|string[] $file the filename,
 * if an array is given it will add all styles
 */
function style($app, $file) {
	if(is_array($file)) {
		foreach($file as $f) {
			OC_Util::addStyle($app, $f);
		}
	} else {
		OC_Util::addStyle($app, $file);
	}
}