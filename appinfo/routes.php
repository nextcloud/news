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

use \OCA\News\AppInfo\Application;

$application = new Application();
$application->registerRoutes($this, ['routes' => [
	// page
	['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	['name' => 'page#settings', 'url' => '/settings', 'verb' => 'GET'],
	['name' => 'page#update_settings', 'url' => '/settings', 'verb' => 'PUT'],
	['name' => 'page#manifest', 'url' => '/manifest.webapp', 'verb' => 'GET'],

	// folders
	['name' => 'folder#index', 'url' => '/folders', 'verb' => 'GET'],
	['name' => 'folder#create', 'url' => '/folders', 'verb' => 'POST'],
	['name' => 'folder#delete', 'url' => '/folders/{folderId}', 'verb' => 'DELETE'],
	['name' => 'folder#restore', 'url' => '/folders/{folderId}/restore', 'verb' => 'POST'],
	['name' => 'folder#rename', 'url' => '/folders/{folderId}/rename', 'verb' => 'POST'],
	['name' => 'folder#read', 'url' => '/folders/{folderId}/read', 'verb' => 'POST'],
	['name' => 'folder#open', 'url' => '/folders/{folderId}/open', 'verb' => 'POST'],

	// feeds
	['name' => 'feed#index', 'url' => '/feeds', 'verb' => 'GET'],
	['name' => 'feed#create', 'url' => '/feeds', 'verb' => 'POST'],
	['name' => 'feed#delete', 'url' => '/feeds/{feedId}', 'verb' => 'DELETE'],
	['name' => 'feed#restore', 'url' => '/feeds/{feedId}/restore', 'verb' => 'POST'],
	['name' => 'feed#move', 'url' => '/feeds/{feedId}/move', 'verb' => 'POST'],
	['name' => 'feed#rename', 'url' => '/feeds/{feedId}/rename', 'verb' => 'POST'],
	['name' => 'feed#read', 'url' => '/feeds/{feedId}/read', 'verb' => 'POST'],
	['name' => 'feed#update', 'url' => '/feeds/{feedId}/update', 'verb' => 'POST'],
	['name' => 'feed#active', 'url' => '/feeds/active', 'verb' => 'GET'],
	['name' => 'feed#import', 'url' => '/feeds/import/articles', 'verb' => 'POST'],

	// items
	['name' => 'item#index', 'url' => '/items', 'verb' => 'GET'],
	['name' => 'item#new_items', 'url' => '/items/new', 'verb' => 'GET'],
	['name' => 'item#readAll', 'url' => '/items/read', 'verb' => 'POST'],
	['name' => 'item#read', 'url' => '/items/{itemId}/read', 'verb' => 'POST'],
	['name' => 'item#read_multiple', 'url' => '/items/read/multiple', 'verb' => 'POST'],
	['name' => 'item#star', 'url' => '/items/{feedId}/{guidHash}/star', 'verb' => 'POST'],

	// export
	['name' => 'export#opml', 'url' => '/export/opml', 'verb' => 'GET'],
	['name' => 'export#articles', 'url' => '/export/articles', 'verb' => 'GET'],

	// API 1.2
	['name' => 'utility_api#version', 'url' => '/api/v1-2/version', 'verb' => 'GET'],
	['name' => 'utility_api#before_update', 'url' => '/api/v1-2/cleanup/before-update', 'verb' => 'GET'],
	['name' => 'utility_api#after_update', 'url' => '/api/v1-2/cleanup/after-update', 'verb' => 'GET'],
	['name' => 'utility_api#preflighted_cors', 'url' => '/api/v1-2/{path}', 'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],

	// folders
	['name' => 'folder_api#index', 'url' => '/api/v1-2/folders', 'verb' => 'GET'],
	['name' => 'folder_api#create', 'url' => '/api/v1-2/folders', 'verb' => 'POST'],
	['name' => 'folder_api#update', 'url' => '/api/v1-2/folders/{folderId}', 'verb' => 'PUT'],
	['name' => 'folder_api#delete', 'url' => '/api/v1-2/folders/{folderId}', 'verb' => 'DELETE'],
	['name' => 'folder_api#read', 'url' => '/api/v1-2/folders/{folderId}/read', 'verb' => 'PUT'], // FIXME: POST would be more correct

	// feeds
	['name' => 'feed_api#index', 'url' => '/api/v1-2/feeds', 'verb' => 'GET'],
	['name' => 'feed_api#create', 'url' => '/api/v1-2/feeds', 'verb' => 'POST'],
	['name' => 'feed_api#update', 'url' => '/api/v1-2/feeds/{feedId}', 'verb' => 'PUT'],
	['name' => 'feed_api#delete', 'url' => '/api/v1-2/feeds/{feedId}', 'verb' => 'DELETE'],
	['name' => 'feed_api#from_all_users', 'url' => '/api/v1-2/feeds/all', 'verb' => 'GET'],
	['name' => 'feed_api#move', 'url' => '/api/v1-2/feeds/{feedId}/move', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'feed_api#rename', 'url' => '/api/v1-2/feeds/{feedId}/rename', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'feed_api#read', 'url' => '/api/v1-2/feeds/{feedId}/read', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'feed_api#update', 'url' => '/api/v1-2/feeds/update', 'verb' => 'GET'],

	// items
	['name' => 'item_api#index', 'url' => '/api/v1-2/items', 'verb' => 'GET'],
	['name' => 'item_api#updated', 'url' => '/api/v1-2/items/updated', 'verb' => 'GET'],
	['name' => 'item_api#read', 'url' => '/api/v1-2/items/{itemId}/read', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#unread', 'url' => '/api/v1-2/items/{itemId}/unread', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#read_all', 'url' => '/api/v1-2/items/read', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#read_multiple', 'url' => '/api/v1-2/items/read/multiple', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#unread_multiple', 'url' => '/api/v1-2/items/unread/multiple', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#star', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/star', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#unstar', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/unstar', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#star_multiple', 'url' => '/api/v1-2/items/star/multiple', 'verb' => 'PUT'], // FIXME: POST would be more correct
	['name' => 'item_api#unstar_multiple', 'url' => '/api/v1-2/items/unstar/multiple', 'verb' => 'PUT'], // FIXME: POST would be more correct
]]);
