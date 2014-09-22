<?php
/**
 * Backports for <7.0.3
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
			\OCP\Util::addScript($app, $f);
		}
	} else {
		\OCP\Util::addScript($app, $file);
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
			\OCP\Util::addStyle($app, $f);
		}
	} else {
		\OCP\Util::addStyle($app, $file);
	}
}

