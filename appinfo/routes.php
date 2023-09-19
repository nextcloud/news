<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Paul Tirk <paultirk@paultirk.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2020 Paul Tirk
 */

return ['routes' => [
// page
['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
['name' => 'page#settings', 'url' => '/settings', 'verb' => 'GET'],
['name' => 'page#update_settings', 'url' => '/settings', 'verb' => 'PUT'],
['name' => 'page#manifest', 'url' => '/manifest.webapp', 'verb' => 'GET'],
['name' => 'page#explore', 'url' => '/explore/feeds.{lang}.json', 'verb' => 'GET'],

// admin
['name' => 'admin#update', 'url' => '/admin', 'verb' => 'PUT'],
['name' => 'admin#migrate', 'url' => '/admin/migrate', 'verb' => 'POST'],

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
['name' => 'feed#read', 'url' => '/feeds/{feedId}/read', 'verb' => 'POST'],
['name' => 'feed#update', 'url' => '/feeds/{feedId}/update', 'verb' => 'POST'],
['name' => 'feed#active', 'url' => '/feeds/active', 'verb' => 'GET'],
['name' => 'feed#import', 'url' => '/feeds/import/articles', 'verb' => 'POST'],
['name' => 'feed#patch', 'url' => '/feeds/{feedId}', 'verb' => 'PATCH'],

// items
['name' => 'item#index', 'url' => '/items', 'verb' => 'GET'],
['name' => 'item#new_items', 'url' => '/items/new', 'verb' => 'GET'],
['name' => 'item#readAll', 'url' => '/items/read', 'verb' => 'POST'],
['name' => 'item#read', 'url' => '/items/{itemId}/read', 'verb' => 'POST'],
['name' => 'item#read_multiple', 'url' => '/items/read/multiple', 'verb' => 'POST'],
['name' => 'item#star', 'url' => '/items/{feedId}/{guidHash}/star', 'verb' => 'POST'],
['name' => 'item#share', 'url' => '/items/{itemId}/share/{shareRecipientId}', 'verb' => 'POST'],

// export
['name' => 'export#opml', 'url' => '/export/opml', 'verb' => 'GET'],
['name' => 'export#articles', 'url' => '/export/articles', 'verb' => 'GET'],

// general API
['name' => 'api#index', 'url' => '/api', 'verb' => 'GET'],

['name' => 'utility_api#preflighted_cors', 'url' => '/api/{apiVersion}/{path}', 'verb' => 'OPTIONS', 'requirements' => ['apiVersion' => '(v1-2|v1-3|v2)', 'path' => '.+']],
['name' => 'utility_api#version', 'url' => '/api/{apiVersion}/version', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3|v2)']],

// API 1.x
['name' => 'utility_api#status', 'url' => '/api/{apiVersion}/status', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'utility_api#before_update', 'url' => '/api/{apiVersion}/cleanup/before-update', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'utility_api#after_update', 'url' => '/api/{apiVersion}/cleanup/after-update', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],

// folders
['name' => 'folder_api#index', 'url' => '/api/{apiVersion}/folders', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'folder_api#create', 'url' => '/api/{apiVersion}/folders', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'folder_api#update', 'url' => '/api/{apiVersion}/folders/{folderId}', 'verb' => 'PUT', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'folder_api#delete', 'url' => '/api/{apiVersion}/folders/{folderId}', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'folder_api#read', 'url' => '/api/{apiVersion}/folders/{folderId}/read', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'folder_api#read', 'url' => '/api/v1-2/folders/{folderId}/read', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3

// feeds
['name' => 'feed_api#index', 'url' => '/api/{apiVersion}/feeds', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'feed_api#create', 'url' => '/api/{apiVersion}/feeds', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'feed_api#update', 'url' => '/api/{apiVersion}/feeds/{feedId}', 'verb' => 'PUT', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'feed_api#delete', 'url' => '/api/{apiVersion}/feeds/{feedId}', 'verb' => 'DELETE', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'feed_api#from_all_users', 'url' => '/api/{apiVersion}/feeds/all', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'feed_api#move', 'url' => '/api/{apiVersion}/feeds/{feedId}/move', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'feed_api#move', 'url' => '/api/v1-2/feeds/{feedId}/move', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'feed_api#rename', 'url' => '/api/{apiVersion}/feeds/{feedId}/rename', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'feed_api#rename', 'url' => '/api/v1-2/feeds/{feedId}/rename', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'feed_api#read', 'url' => '/api/{apiVersion}/feeds/{feedId}/read', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'feed_api#read', 'url' => '/api/v1-2/feeds/{feedId}/read', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'feed_api#update', 'url' => '/api/{apiVersion}/feeds/update', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],

// items
['name' => 'item_api#index', 'url' => '/api/{apiVersion}/items', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'item_api#updated', 'url' => '/api/{apiVersion}/items/updated', 'verb' => 'GET', 'requirements' => ['apiVersion' => '(v1-2|v1-3)']],
['name' => 'item_api#read', 'url' => '/api/{apiVersion}/items/{itemId}/read', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#read', 'url' => '/api/v1-2/items/{itemId}/read', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#unread', 'url' => '/api/{apiVersion}/items/{itemId}/unread', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#unread', 'url' => '/api/v1-2/items/{itemId}/unread', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#read_all', 'url' => '/api/{apiVersion}/items/read', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#read_all', 'url' => '/api/v1-2/items/read', 'verb' => 'PUT', 'postfix' => 'v1.2'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#read_multiple_by_ids', 'url' => '/api/{apiVersion}/items/read/multiple', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#read_multiple', 'url' => '/api/v1-2/items/read/multiple', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#unread_multiple_by_ids', 'url' => '/api/{apiVersion}/items/unread/multiple', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#unread_multiple', 'url' => '/api/v1-2/items/unread/multiple', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#star_by_item_id', 'url' => '/api/{apiVersion}/items/{itemId}/star', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#star', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/star', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#unstar_by_item_id', 'url' => '/api/{apiVersion}/items/{itemId}/unstar', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#unstar', 'url' => '/api/v1-2/items/{feedId}/{guidHash}/unstar', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#star_multiple_by_item_ids', 'url' => '/api/{apiVersion}/items/star/multiple', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#star_multiple', 'url' => '/api/v1-2/items/star/multiple', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3
['name' => 'item_api#unstar_multiple_by_item_ids', 'url' => '/api/{apiVersion}/items/unstar/multiple', 'verb' => 'POST', 'requirements' => ['apiVersion' => '(v1-3)']],
['name' => 'item_api#unstar_multiple', 'url' => '/api/v1-2/items/unstar/multiple', 'verb' => 'PUT'], // Backward compatibility. Corrected HTTP method as of v1.3

// API 2
['name' => 'folder_api_v2#create', 'url' => '/api/v2/folders', 'verb' => 'POST'],
['name' => 'folder_api_v2#update', 'url' => '/api/v2/folders/{folderId}', 'verb' => 'PATCH'],
['name' => 'folder_api_v2#delete', 'url' => '/api/v2/folders/{folderId}', 'verb' => 'DELETE'],

]];
