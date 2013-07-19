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
		App::main('PageController', 'index', $params, new DIContainer());
	}
);

/**
 * Folders
 */
$this->create('news_folders', '/folders')->get()->action(
	function($params){
		App::main('FolderController', 'folders', $params, new DIContainer());
	}
);

$this->create('news_folders_open', '/folders/{folderId}/open')->post()->action(
	function($params){
		App::main('FolderController', 'open', $params, new DIContainer());
	}
);

$this->create('news_folders_collapse', '/folders/{folderId}/collapse')->post()->action(
	function($params){
		App::main('FolderController', 'collapse', $params, new DIContainer());
	}
);

$this->create('news_folders_create', '/folders')->post()->action(
	function($params){
		App::main('FolderController', 'create', $params, new DIContainer());
	}
);

$this->create('news_folders_delete', '/folders/{folderId}')->delete()->action(
	function($params){
		App::main('FolderController', 'delete', $params, new DIContainer());
	}
);

$this->create('news_folders_restore', '/folders/{folderId}/restore')->post()->action(
	function($params){
		App::main('FolderController', 'restore', $params, new DIContainer());
	}
);

$this->create('news_folders_rename', '/folders/{folderId}/rename')->post()->action(
	function($params){
		App::main('FolderController', 'rename', $params, new DIContainer());
	}
);

$this->create('news_folders_read', '/folders/{folderId}/read')->post()->action(
	function($params){
		App::main('FolderController', 'read', $params, new DIContainer());
	}
);

/**
 * Feeds
 */
$this->create('news_feeds', '/feeds')->get()->action(
	function($params){
		App::main('FeedController', 'feeds', $params, new DIContainer());
	}
);

$this->create('news_feeds_active', '/feeds/active')->get()->action(
	function($params){
		App::main('FeedController', 'active', $params, new DIContainer());
	}
);

$this->create('news_feeds_create', '/feeds')->post()->action(
	function($params){
		App::main('FeedController', 'create', $params, new DIContainer());
	}
);

$this->create('news_feeds_delete', '/feeds/{feedId}')->delete()->action(
	function($params){
		App::main('FeedController', 'delete', $params, new DIContainer());
	}
);

$this->create('news_feeds_restore', '/feeds/{feedId}/restore')->post()->action(
	function($params){
		App::main('FeedController', 'restore', $params, new DIContainer());
	}
);

$this->create('news_feeds_update', '/feeds/{feedId}/update')->post()->action(
	function($params){
		App::main('FeedController', 'update', $params, new DIContainer());
	}
);

$this->create('news_feeds_move', '/feeds/{feedId}/move')->post()->action(
	function($params){
		App::main('FeedController', 'move', $params, new DIContainer());
	}
);

$this->create('news_feeds_read', '/feeds/{feedId}/read')->post()->action(
	function($params){
		App::main('FeedController', 'read', $params, new DIContainer());
	}
);

$this->create('news_feeds_import_googlereader', '/feeds/import/googlereader')
->post()->action(
	function($params){
		App::main('FeedController', 'importGoogleReader', $params,
			new DIContainer());
	}
);

/**
 * Items
 */
$this->create('news_items', '/items')->get()->action(
	function($params){
		App::main('ItemController', 'items', $params, new DIContainer());
	}
);

$this->create('news_items_read', '/items/{itemId}/read')->post()->action(
	function($params){
		App::main('ItemController', 'read', $params, new DIContainer());
	}
);

$this->create('news_items_unread', '/items/{itemId}/unread')->post()->action(
	function($params){
		App::main('ItemController', 'unread', $params, new DIContainer());
	}
);

$this->create('news_items_star', '/items/{feedId}/{guidHash}/star')->post()->action(
	function($params){
		App::main('ItemController', 'star', $params, new DIContainer());
	}
);

$this->create('news_items_unstar', '/items/{feedId}/{guidHash}/unstar')->post()->action(
	function($params){
		App::main('ItemController', 'unstar', $params, new DIContainer());
	}
);

$this->create('news_items_all_read', '/items/read')->post()->action(
	function($params){
		App::main('ItemController', 'readAll', $params, new DIContainer());
	}
);

/**
 * Export
 */
$this->create('news_export_opml', '/export/opml')->get()->action(
	function($params){
		App::main('ExportController', 'opml', $params, new DIContainer());
	}
);

/**
 * User Settings
 */
$this->create('news_usersettings_read', '/usersettings/read')->get()->action(
	function($params){
		App::main('UserSettingsController', 'read', $params, new DIContainer());
	}
);

$this->create('news_usersettings_read_show', '/usersettings/read/show')->post()->action(
	function($params){
		App::main('UserSettingsController', 'show', $params, new DIContainer());
	}
);

$this->create('news_usersettings_read_hide', '/usersettings/read/hide')->post()->action(
	function($params){
		App::main('UserSettingsController', 'hide', $params, new DIContainer());
	}
);

$this->create('news_usersettings_language', '/usersettings/language')->get()->action(
	function($params){
		App::main('UserSettingsController', 'getLanguage', $params, new DIContainer());
	}
);


/**
 * Generic API
 */
$this->create('news_api_version', '/api/v1-2/version')->get()->action(
	function($params) {
		return App::main('NewsAPI', 'version', $params, new DIContainer());
	}
);

/**
 * Folder API
 */
$this->create('news_api_folders_get_all', '/api/v1-2/folders')->get()->action(
	function($params) {
		return App::main('FolderAPI', 'getAll', $params, new DIContainer());
	}
);

$this->create('news_api_folders_create', '/api/v1-2/folders')->post()->action(
	function($params) {
		return App::main('FolderAPI', 'create', $params, new DIContainer());
	}
);

$this->create('news_api_folders_delete', '/api/v1-2/folders/{folderId}')->delete()->action(
	function($params) {
		return App::main('FolderAPI', 'delete', $params, new DIContainer());
	}
);

$this->create('news_api_folders_update', '/api/v1-2/folders/{folderId}')->put()->action(
	function($params) {
		return App::main('FolderAPI', 'update', $params, new DIContainer());
	}
);

$this->create('news_api_folders_read', '/api/v1-2/folders/{folderId}/read')->put()->action(
	function($params) {
		return App::main('FolderAPI', 'read', $params, new DIContainer());
	}
);

/**
 * Feed API
 */
$this->create('news_api_feeds_get_all', '/api/v1-2/feeds')->get()->action(
	function($params) {
		return App::main('FeedAPI', 'getAll', $params, new DIContainer());
	}
);

$this->create('news_api_feeds_create', '/api/v1-2/feeds')->post()->action(
	function($params) {
		return App::main('FeedAPI', 'create', $params, new DIContainer());
	}
);

$this->create('news_api_feeds_delete', '/api/v1-2/feeds/{feedId}')->delete()->action(
	function($params) {
		return App::main('FeedAPI', 'delete', $params, new DIContainer());
	}
);

$this->create('news_api_feeds_move', '/api/v1-2/feeds/{feedId}/move')->put()->action(
	function($params) {
		return App::main('FeedAPI', 'move', $params, new DIContainer());
	}
);

$this->create('news_api_feeds_read', '/api/v1-2/feeds/{feedId}/read')->put()->action(
	function($params) {
		return App::main('FeedAPI', 'read', $params, new DIContainer());
	}
);

/**
 * Item API
 */
$this->create('news_api_items_get_all', '/api/v1-2/items')->get()->action(
	function($params) {
		return App::main('ItemAPI', 'getAll', $params, new DIContainer());
	}
);

$this->create('news_api_items_updated', '/api/v1-2/items/updated')->get()->action(
	function($params) {
		return App::main('ItemAPI', 'getUpdated', $params, new DIContainer());
	}
);

$this->create('news_api_items_read', '/api/v1-2/items/{itemId}/read')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'read', $params, new DIContainer());
	}
);

$this->create('news_api_items_unread', '/api/v1-2/items/{itemId}/unread')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'unread', $params, new DIContainer());
	}
);

$this->create('news_api_items_star', '/api/v1-2/items/{feedId}/{guidHash}/star')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'star', $params, new DIContainer());
	}
);

$this->create('news_api_items_unstar', '/api/v1-2/items/{feedId}/{guidHash}/unstar')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'unstar', $params, new DIContainer());
	}
);

$this->create('news_api_items_read_all', '/api/v1-2/items/read')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'readAll', $params, new DIContainer());
	}
);

$this->create('news_api_items_read_multiple', '/api/v1-2/items/read/multiple')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'readMultiple', $params, new DIContainer());
	}
);

$this->create('news_api_items_unread_multiple', '/api/v1-2/items/unread/multiple')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'unreadMultiple', $params, new DIContainer());
	}
);

$this->create('news_api_items_star_multiple', '/api/v1-2/items/star/multiple')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'starMultiple', $params, new DIContainer());
	}
);

$this->create('news_api_items_unstar_multiple', '/api/v1-2/items/unstar/multiple')->put()->action(
	function($params) {
		return App::main('ItemAPI', 'unstarMultiple', $params, new DIContainer());
	}
);
