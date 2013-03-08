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

require_once \OC_App::getAppPath('news') . '/appinfo/bootstrap.php';

/**
 * Shortcut for calling a controller method and printing the result
 * @param string $controllerName: the name of the controller under which it is
 *                                stored in the DI container
 * @param string $methodName: the method that you want to call
 * @param array $urlParams: an array with variables extracted from the routes
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 * @param bool $isAjax: if the request is an ajax request
 */
function callController($controllerName, $methodName, $urlParams, $disableAdminCheck=true,
						$isAjax=false){
	$container = createDIContainer();

	// run security checks
	$security = $container['Security'];
	runSecurityChecks($security, $isAjax, $disableAdminCheck);

	// call the controller and render the page
	$controller = $container[$controllerName];
	$response = $controller->$methodName($urlParams);
	echo $response->render();
}


/**
 * Shortcut for calling an ajax controller method and printing the result
 * @param string $controllerName: the name of the controller under which it is
 *                                stored in the DI container
 * @param string $methodName: the method that you want to call
 * @param array $urlParams: an array with variables extracted from the routes
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 */
function callAjaxController($controllerName, $methodName, $urlParams, $disableAdminCheck=true){
	callController($controllerName, $methodName, $urlParams, $disableAdminCheck, true);
}


/**
 * Runs the security checks and exits on error
 * @param Security $security: the security object
 * @param bool $isAjax: if true, the ajax checks will be run, otherwise the normal
 *                      checks
 * @param bool $disableAdminCheck: disables the check for adminuser rights
 */
function runSecurityChecks($security, $isAjax=false, $disableAdminCheck=true){
	if($disableAdminCheck){
		$security->setIsAdminCheck(false);
	}

	if($isAjax){
		$security->runAJAXChecks();
	} else {
		$security->runChecks();
	}
}


/*************************
 * Define your routes here
 */


/**
 * Normal Routes
 */
$this->create('news_index', '/')->action(
	function($params){
		callController('NewsController', 'index', $params, true);
	}
);

$this->create('news_index_feed', '/feed/{feedid}')->action(
	function($params){
		callController('NewsController', 'index', $params, true);
	}
);

$this->create('news_export_opml', '/export/opml')->action(
	function($params){
		callController('NewsController', 'exportOPML', $params, true);
	}
);


/**
 * AJAX Routes
 */
$this->create('news_ajax_init', '/ajax/init')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'init', $params);
	}
);

$this->create('news_ajax_setshowall', '/ajax/setshowall')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'setShowAll', $params);
	}
);


/**
 * Folders
 */
$this->create('news_ajax_collapsefolder', '/ajax/collapsefolder')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'collapseFolder', $params);
	}
);

$this->create('news_ajax_changefoldername', '/ajax/changefoldername')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'changeFolderName', $params);
	}
);

$this->create('news_ajax_createfolder', '/ajax/createfolder')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'createFolder', $params);
	}
);

$this->create('news_ajax_deletefolder', '/ajax/deletefolder')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'deleteFolder', $params);
	}
);


/**
 * Feeds
 */
$this->create('news_ajax_loadfeed', '/ajax/loadfeed')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'loadFeed', $params);
	}
);

$this->create('news_ajax_deletefeed', '/ajax/deletefeed')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'deleteFeed', $params);
	}
);

$this->create('news_ajax_movefeedtofolder', '/ajax/movefeedtofolder')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'moveFeedToFolder', $params);
	}
);

$this->create('news_ajax_updatefeed', '/ajax/updatefeed')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'updateFeed', $params);
	}
);

$this->create('news_ajax_createfeed', '/ajax/createfeed')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'createFeed', $params);
	}
);


/**
 * Items
 */
$this->create('news_ajax_setitemstatus', '/ajax/setitemstatus')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'setItemStatus', $params);
	}
);

$this->create('news_ajax_setallitemsread', '/ajax/setallitemsread')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'setAllItemsRead', $params);
	}
);


/**
 * Import stuff
 */
$this->create('news_ajax_importOPML', '/import')->action(
	function($params){
		callAjaxController('NewsAjaxController', 'uploadOPML', $params);
	}
);


/**
 * External API
 */

/**
 * Feed API
 */

\OCP\API::register(
	'get', '/news/feeds',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FeedApi']->getAll($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
\OCP\API::register(
	'get', '/news/feeds/{feedid}',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FeedApi']->getById($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
\OCP\API::register(
	'post', '/news/feeds/create',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FeedApi']->create($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
\OCP\API::register(
	'post', '/news/feeds/{feedid}/delete',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FeedApi']->delete($urlParams);
	},
	'news', \OC_API::USER_AUTH
);

/**
 * Folder API
 */

\OCP\API::register(
	'get', '/news/folders',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FolderApi']->getAll($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
\OCP\API::register(
	'post', '/news/folders/create',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FolderApi']->create($urlParams);
	},
	'news', \OC_API::USER_AUTH
);

\OCP\API::register(
	'get', '/news/folders/{folderid}/delete',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FolderApi']->delete($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
\OCP\API::register(
	'post', '/news/folders/{folderid}/modify',
	function($urlParams) {
		$container = createDIContainer();
		return $container['FolderApi']->modify($urlParams);
	},
	'news', \OC_API::USER_AUTH
);
