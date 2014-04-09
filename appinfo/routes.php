<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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

use \OCP\AppFramework\App;

use \OCA\News\App\News;

$app = new News();
$app->registerRoutes(array(
  'routes' => array(
  	///////////////// Website
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

    ///////////////// API
    // Generic
    array('name' => 'api#version', 'url' => '/api/v1-2/version', 'verb' => 'GET'),
    array('name' => 'api#update', 'url' => '/api/v1-2/update', 'verb' => 'GET'),
    array('name' => 'api#before_update', 'url' => '/api/v1-2/cleanup/before-update', 'verb' => 'GET'),
    array('name' => 'api#after_update', 'url' => '/api/v1-2/cleanup/after-update', 'verb' => 'GET'),

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

    // items
    array('name' => 'item_api#index', 'url' => '/api/v1-2/items', 'verb' => 'GET'),
    array('name' => 'item_api#updated', 'url' => '/api/v1-2/items/updated', 'verb' => 'GET'),
    array('name' => 'item_api#read', 'url' => '/api/v1-2/feeds/{itemId}/read', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#unread', 'url' => '/api/v1-2/feeds/{itemId}/unread', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#read_all', 'url' => '/api/v1-2/feeds/read', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#read_multiple', 'url' => '/api/v1-2/feeds/read/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#unread_multiple', 'url' => '/api/v1-2/feeds/unread/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#star', 'url' => '/api/v1-2/feeds/{feedId}/{guidHash}/star', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#unstar', 'url' => '/api/v1-2/feeds/{feedId}/{guidHash}/unstar', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#star_multiple', 'url' => '/api/v1-2/feeds/star/multiple', 'verb' => 'PUT'), // FIXME: POST would be more correct
    array('name' => 'item_api#unstar_multiple', 'url' => '/api/v1-2/feeds/unstar/multiple', 'verb' => 'PUT') // FIXME: POST would be more correct
  )
));

/* TODO: FIX CORS
$this->create('news_api_cors', '/api/v1-2/{path}')->method('options')->action(
	function($params) {
		return App::main('NewsAPI', 'cors', $params, new DIContainer());
	}
)->requirements(array('path' => '.+'));
*/