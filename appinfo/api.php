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

use \OCA\News\DependencyInjection\DIContainer;
use \OCA\AppFramework\External\External;


/**
 * Generic API
 */
\OCP\API::register('get', '/apps/news/version',
	function($params) {
		return External::main('NewsAPI', 'version', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

/**
 * Folder API
 */
\OCP\API::register('get', '/apps/news/folders',
	function($params) {
		return External::main('FolderAPI', 'getAll', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('post', '/apps/news/folders',
	function($params) {
		return External::main('FolderAPI', 'create', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('delete', '/apps/news/folders/{folderId}',
	function($params) {
		return External::main('FolderAPI', 'delete', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/folders/{folderId}',
	function($params) {
		return External::main('FolderAPI', 'update', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);


/**
 * Feed API
 */
\OCP\API::register('get', '/apps/news/feeds',
	function($params) {
		return External::main('FeedAPI', 'getAll', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('post', '/apps/news/feeds/{feedId}',
	function($params) {
		return External::main('FeedAPI', 'create', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('delete', '/apps/news/feeds/{feedId}',
	function($params) {
		return External::main('FeedAPI', 'delete', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/feeds/{feedId}/move',
	function($params) {
		return External::main('FeedAPI', 'move', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/feeds/{feedId}/read',
	function($params) {
		return External::main('FeedAPI', 'read', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

/**
 * Item API
 */
\OCP\API::register('get', '/apps/news/items',
	function($params) {
		return External::main('ItemAPI', 'getAll', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('get', '/apps/news/items/updated',
	function($params) {
		return External::main('ItemAPI', 'getUpdated', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/items/{itemId}/read',
	function($params) {
		return External::main('ItemAPI', 'read', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/items/{itemId}/unread',
	function($params) {
		return External::main('ItemAPI', 'unread', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/items/{feedId}/{guidHash}/star',
	function($params) {
		return External::main('ItemAPI', 'star', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);

\OCP\API::register('put', '/apps/news/items/{feedId}/{guidHash}/unstar',
	function($params) {
		return External::main('ItemAPI', 'unstar', $params, new DIContainer());
	}, 
	'news', 
	\OC_API::USER_AUTH
);