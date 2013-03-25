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
				@getItems @_activeFeed.getType(), @_activeFeed.getId(), 0, =>
					@_loading.decrease()
			
			triggerHideRead = =>
				@_triggerHideRead

			@getAllFolders(triggerHideRead)
			@getAllFeeds(triggerHideRead)
			@userSettingsRead(triggerHideRead)
			@getStarredItems(triggerHideRead)
			

		###
			ITEM CONTROLLER
		###
		getItems: (type, id, offset, onSuccess=null, updatedSince=null) ->

			onSuccess or= ->

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

			params =
				data: data
				onSuccess: onSuccess

			@_request.get 'news_items', params


		getStarredItems: (onSuccess) ->
			params =
				onSuccess: onSuccess
			@_request.get 'news_items_starred', params


		starItem: (itemId) ->
			###
			Stars an item
			###
			params =
				urlParams:
					itemId: itemId

			@_request.post 'news_items_star', params



		unstarItem: (itemId) ->
			###
			Unstars an item
			###
			params =
				urlParams:
					itemId: itemId

			@_request.post 'news_items_unstar', params


		readItem: (itemId) ->
			###
			Sets an item as read
			###
			params =
				urlParams:
					itemId: itemId

			@_request.post 'news_items_read', params



		unreadItem: (itemId) ->
			###
			Sets an item as unread
			###
			params =
				urlParams:
					itemId: itemId

			@_request.post 'news_items_unread', params


		###
			FEED CONTROLLER
		###
		getAllFeeds: (callback) ->
			callback or= ->
			params =
				onSuccess: callback

			@_request.get 'news_feeds', params


		getActiveFeed: (onSuccess) ->
			params =
				onSuccess: onSuccess

			@_request.get 'news_feeds_active', params


		createFeed: (url, parentFolderId, onSuccess, onFailure) ->
			params =
				data:
					parentFolderId: parentFolderId
					url: url
				onSuccess: onSuccess
				onFailure: onFailure

			@_request.post 'news_feeds_create', params


		deleteFeed: (feedId) ->
			params =
				urlParams:
					feedId: feedId

			@_request.post 'news_feeds_delete', params


		moveFeed: (feedId, folderId) ->
			###
			moves a feed to a new folder
			###
			params =
				urlParams:
					feedId: feedId
				data:
					folderId: folderId

			@_request.post 'news_feeds_move', params


		setFeedRead: (feedId, highestItemId) ->
			###
			sets all items of a feed as read
			###
			params =
				urlParams:
					feedId: feedId
				data:
					highestItemId: highestItemId

			@_request.post 'news_feeds_read', params


		updateFeed: (feedId) ->
			###
			moves a feed to a new folder
			###
			params =
				urlParams:
					feedId: feedId

			@_request.post 'news_feeds_update', params


		###
			FOLDER CONTROLLER
		###
		getAllFolders: (callback) ->
			callback or= ->
			params =
				onSuccess: callback

			@_request.get 'news_folders', params

	
		openFolder: (folderId) ->
			###
			Save if a folder was opened
			###
			params =
				urlParams:
					folderId: folderId

			@_request.post 'news_folders_open', params


		collapseFolder: (folderId) ->
			###
			Save if a folder was collapsed
			###
			params =
				urlParams:
					folderId: folderId

			@_request.post 'news_folders_collapse', params


		createFolder: (folderName, parentFolderId=0, onSuccess=null,
						onFailure=null) ->
			onSuccess or= ->
			onFailure or= ->

			params =
				data:
					folderName: folderName
					parentFolderId: parentFolderId
				onSuccess: onSuccess
				onFailure: onFailure

			@_request.post 'news_folders_create', params


		deleteFolder: (folderId) ->
			###
			Save if a folder was collapsed
			###
			params =
				urlParams:
					folderId: folderId


			@_request.post 'news_folders_delete', params


		renameFolder: (folderId, folderName) ->
			###
			Save if a folder was collapsed
			###
			params =
				urlParams:
					folderId: folderId
				data:
					folderName: folderName

			@_request.post 'news_folders_rename', params



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
			callback or= ->
			params =
				onSuccess: callback

			@_request.get 'news_usersettings_read', params


		userSettingsReadShow: ->
			###
			Sets the reader mode to show all
			###
			@_request.post 'news_usersettings_read_show'


		userSettingsReadHide: ->
			###
			Sets the reader mode to show only unread
			###
			@_request.post 'news_usersettings_read_hide'


		_triggerHideRead: ->
			@_$rootScope.$broadcast('triggerHideRead')


	return Persistence

