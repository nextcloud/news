<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

namespace OCA\News;

use \OCA\AppFramework\App;

use \OCA\News\DependencyInjection\DIContainer;


/**
 * Webinterface
 */

$this->create('news_index', '/')->get()->action(
	function($params){
		//App::main('FolderController', 'getAll', $params, new DIContainer());
	}
);


$this->create('news_folders', '/folders')->get()->action(
	function($params){
		App::main('FolderController', 'getAll', $params, new DIContainer());
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
