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

namespace OCA\News;

use \OCP\AppFramework\App;

use \OCA\News\App\News;

$application = new News();
$application->registerRoutes($this, array('routes' => array(
	// page
	array('name' => 'page#index', 'url' => '/', 'verb' => 'GET'),
	array('name' => 'page#settings', 'url' => '/settings', 'verb' => 'GET'),
	array('name' => 'page#update_settings', 'url' => '/settings', 'verb' => 'POST'),

	// folders
	array('name' => 'folder#index', 'url' => '/folders', 'verb' => 'GET'),
	array('name' => 'folder#create', 'url' => '/folders', 'verb' => 'POST'),
	array('name' => 'folder#delete', 'url' => '/folders/{folderId}', 'verb' => 'DELETE'),
	array('name' => 'folder#restore', 'url' => '/folders/{folderId}/restore', 'verb' => 'POST'),
	array('name' => 'folder#rename', 'url' => '/folders/{folderId}/rename', 'verb' => 'POST'),
	array('name' => 'folder#read', 'url' => '/folders/{folderId}/read', 'verb' => 'POST'),
	array('name' => 'folder#open', 'url' => '/folders/{folderId}/open', 'verb' => 'POST'),
	array('name' => 'folder#collapse', 'url' => '/folders/{folderId}/collapse', 'verb' => 'POST'),

	// feeds
	array('name' => 'feed#index', 'url' => '/feeds', 'verb' => 'GET'),
	array('name' => 'feed#create', 'url' => '/feeds', 'verb' => 'POST'),
	array('name' => 'feed#delete', 'url' => '/feeds/{feedId}', 'verb' => 'DELETE'),
	array('name' => 'feed#restore', 'url' => '/feeds/{feedId}/restore', 'verb' => 'POST'),
	array('name' => 'feed#move', 'url' => '/feeds/{feedId}/move', 'verb' => 'POST'),
	array('name' => 'feed#rename', 'url' => '/feeds/{feedId}/rename', 'verb' => 'POST'),
	array('name' => 'feed#read', 'url' => '/feeds/{feedId}/read', 'verb' => 'POST'),
	array('name' => 'feed#update', 'url' => '/feeds/{feedId}/update', 'verb' => 'POST'),
	array('name' => 'feed#active', 'url' => '/feeds/active', 'verb' => 'GET'),
	array('name' => 'feed#import', 'url' => '/feeds/import/articles', 'verb' => 'POST'),

	// items
	array('name' => 'item#index', 'url' => '/items', 'verb' => 'GET'),
	array('name' => 'item#new_items', 'url' => '/items/new', 'verb' => 'GET'),
	array('name' => 'item#readAll', 'url' => '/items/read', 'verb' => 'POST'),
	array('name' => 'item#read', 'url' => '/items/{itemId}/read', 'verb' => 'POST'),
	array('name' => 'item#unread', 'url' => '/items/{itemId}/unread', 'verb' => 'POST'),
	array('name' => 'item#star', 'url' => '/items/{feedId}/{guidHash}/star', 'verb' => 'POST'),
	array('name' => 'item#unstar', 'url' => '/items/{feedId}/{guidHash}/unstar', 'verb' => 'POST'),

	// export
	array('name' => 'export#opml', 'url' => '/export/opml', 'verb' => 'GET'),
	array('name' => 'export#articles', 'url' => '/export/articles', 'verb' => 'GET'),

	// API
	array('name' => 'api#version', 'url' => '/api/v1-2/version', 'verb' => 'GET'),
	array('name' => 'api#before_update', 'url' => '/api/v1-2/cleanup/before-update', 'verb' => 'GET'),
	array('name' => 'api#after_update', 'url' => '/api/v1-2/cleanup/after-update', 'verb' => 'GET'),
	array('name' => 'api#cors', 'url' => '/api/v1-2/{path}', 'verb' => 'OPTIONS', 'requirements' => array('path' => '.+')),

	// folders
	array('name' => 'folder_api#index', 'url' => '/api/v1-2/folders', 'verb' => 'GET'),
	array('name' => 'folder_api#create', 'url' => '/api/v1-2/folders', 'verb' => 'POST'),
	array('name' => 'folder_api#put', 'url' => '/api/v1-2/folders/{folderId}', 'verb' => 'PUT'),
	array('name' => 'folder_api#delete', 'url' => '/api/v1-2/folders/{folderId}', 'verb' => 'DELETE'),
	array('name' => 'folder_api#read', 'url' => '/api/v1-2/folders/{folderId}/read', 'verb' => 'PUT'), // FIXME: POST would be more correct

	// feeds
	array('name' => 'feed_api#index', 'url' => '/api/v1-2/feeds', 'verb' => 'GET'),
	array('name' => 'feed_api#create', 'url' => '/api/v1-2/feeds', 'verb' => 'POST'),
	array('name' => 'feed_api#put', 'url' => '/api/v1-2/feeds/{feedId}', 'verb' => 'PUT'),
	array('name' => 'feed_api#delete', 'url' => '/api/v1-2/feeds/{feedId}', 'verb' => 'DELETE'),
	array('name' => 'feed_api#from_all_users', 'url' => '/api/v1-2/feeds/all', 'verb' => 'GET'),
	array('name' => 'feed_api#move', 'url' => '/api/v1-2/feeds/{feedId}/move', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'feed_api#rename', 'url' => '/api/v1-2/feeds/{feedId}/rename', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'feed_api#read', 'url' => '/api/v1-2/feeds/{feedId}/read', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'feed_api#update', 'url' => '/api/v1-2/update', 'verb' => 'GET'),

	// items
	array('name' => 'item_api#index', 'url' => '/api/v1-2/items', 'verb' => 'GET'),
	array('name' => 'item_api#updated', 'url' => '/api/v1-2/items/updated', 'verb' => 'GET'),
	array('name' => 'item_api#read', 'url' => '/api/v1-2/items/{itemId}/read', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#unread', 'url' => '/api/v1-2/items/{itemId}/unread', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#read_all', 'url' => '/api/v1-2/items/read', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#read_multiple', 'url' => '/api/v1-2/items/read/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#unread_multiple', 'url' => '/api/v1-2/items/unread/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#star', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/star', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#unstar', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/unstar', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#star_multiple', 'url' => '/api/v1-2/items/star/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
	array('name' => 'item_api#unstar_multiple', 'url' => '/api/v1-2/items/unstar/multiple', 'verb' => 'PUT') // FIXME: POST would be more correct
)));

