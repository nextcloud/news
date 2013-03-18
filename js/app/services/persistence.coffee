###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt nukeawhale@gmail.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###


angular.module('News').factory '_Persistence', ->
	
	class Persistence

		constructor: (@_request, @_loading, @_config, @_activeFeed,
						@_$rootScope) ->


		init: ->
			###
			Loads the initial data from the server
			###
			@_loading.increase()

			# items can only be loaded after the active feed is known
			@getActiveFeed =>
				@getItems @_activeFeed.getType(), @_activeFeed.getId(), null, =>
					@_loading.decrease()
			
			@getAllFolders(@_triggerHideRead)
			@getAllFeeds(@_triggerHideRead)
			@userSettingsRead(@_triggerHideRead)
			@getStarredItems(@_triggerHideRead)
			

		###
			ITEM CONTROLLER
		###
		getItems: (type, id, offset, onSuccess, updatedSince=null) ->
			# TODO
			if updatedSince != null
				data =
					updatedSince: updatedSince
					type: type
					id: id
			else
				data =
					limit: @_config.itemBatchSize
					offset: offset
					id: id
					type: type

			@_request.get 'news_items', {}, data, onSuccess


		getItemById: (itemId) ->
			url =
				itemId: itemId

			@_request.get 'news_item', url


		getStarredItems: (onSuccess) ->
			@_request.get 'news_starred_items', {}, {}, onSuccess


		starItem: (itemId) ->
			###
			Stars an item
			###
			url =
				itemId: itemId

			@_request.post 'news_star_item', url



		unstarItem: (itemId) ->
			###
			Unstars an item
			###
			url =
				itemId: itemId

			@_request.post 'news_unstar_item', url


		readItem: (itemId) ->
			###
			Sets an item as read
			###
			url =
				itemId: itemId

			@_request.post 'news_read_item', url



		unreadItem: (itemId) ->
			###
			Sets an item as unread
			###
			url =
				itemId: itemId

			@_request.post 'news_unread_item', url


		###
			FOLDER CONTROLLER
		###
		getAllFolders: (callback) ->
			callback or= angular.noop
			@_request.get 'news_folders', {}, {}, callback

	
		getFolderById: (folderId) ->
			url =
				folderId: folderId
			@_request.get 'news_folder', url


		openFolder: (folderId) ->
			###
			Save if a folder was opened
			###
			url =
				folderId: folderId

			@_request.post 'news_open_folder', url


		collapseFolder: (folderId) ->
			###
			Save if a folder was collapsed
			###
			url =
				folderId: folderId

			@_request.post 'news_collapse_folder', url


		createFolder: (folderName, parentFolderId=0, onSuccess=null, onError=null) ->
			data =
				folderName: folderName
				parentFolderId: parentFolderId
			onSuccess or= angular.noop
			onError or= angular.noop

			@_request.post 'news_create_folder', {}, data, onSuccess, onError


		deleteFolder: (folderId) ->
			###
			Save if a folder was collapsed
			###
			url =
				folderId: folderId

			@_request.post 'news_delete_folder', url


		renameFolder: (folderId, folderName) ->
			###
			Save if a folder was collapsed
			###
			url =
				folderId: folderId

			data =
				folderName: folderName

			@_request.post 'news_rename_folder', url, data


		###
			FEED CONTROLLER
		###
		getAllFeeds: (callback) ->
			callback or= angular.noop

			@_request.get 'news_feeds', {}, {}, callback


		getFeedById: (feedId) ->
			url =
				feedId: feedId

			@_request.get 'news_feed', url


		getActiveFeed: (onSuccess) ->
			@_request.get 'news_active_feed', {}, {}, onSuccess


		createFeed: (url, parentFolderId, onSuccess, onError) ->
			data =
				parentFolderId: parentFolderId
				url: url

			@_request.post 'news_create_feed', {}, data, onSuccess, onError


		deleteFeed: (feedId) ->
			url =
				feedId: feedId

			@_request.post 'news_delete_feed', url


		moveFeed: (feedId, folderId) ->
			###
			moves a feed to a new folder
			###
			url =
				feedId: feedId
			data =
				folderId: folderId

			@_request.post 'news_move_feed', url, data


		setFeedRead: (feedId, highestItemId) ->
			###
			sets all items of a feed as read
			###
			url =
				feedId: feedId
			data =
				highestItemId: highestItemId

			@_request.post 'news_set_feed_read', url, data


		updateFeed: (feedId) ->
			###
			moves a feed to a new folder
			###
			url =
				feedId: feedId

			@_request.post 'news_update_feed', url


		###
			EXPORT CONTROLLER
		###
		exportOPML: ->
			###
			Prompts for an OPML download
			###
			@_request.get 'news_export_opml'


		###
			USERSETTINGS CONTROLLER
		###
		userSettingsRead: (callback=null) ->
			###
			Gets the configs for read settings
			###
			callback or= angular.noop
			@_request.get 'news_user_settings_read', {}, {}, callback


		userSettingsReadShow: ->
			###
			Sets the reader mode to show all
			###
			@_request.post 'news_user_settings_read_show'


		userSettingsReadHide: ->
			###
			Sets the reader mode to show only unread
			###
			@_request.post 'news_user_settings_read_hide'


		_trigerHideRead: ->
			@_$rootScope.$broadcast('triggerHideRead')


	return Persistence

