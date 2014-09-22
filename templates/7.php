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

/**
 * Shortcut for HTML imports
 * @param string $app the appname
 * @param string|string[] $file the path relative to the app's component folder,
 * if an array is given it will add all components
 */
function component($app, $file) {
	if(is_array($file)) {
		foreach($file as $f) {
			$url = link_to($app, 'component/' . $f . '.html');
			OC_Util::addHeader('link', array('rel' => 'import', 'href' => $url));
		}
	} else {
		$url = link_to($app, 'component/' . $file . '.html');
		OC_Util::addHeader('link', array('rel' => 'import', 'href' => $url));
	}
}