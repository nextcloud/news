###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt dev@bernhard-posselt.com

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

angular.module('News').factory 'Persistence',
['Request', 'FeedLoading', 'AutoPageLoading', 'NewLoading', 'Config',
'ActiveFeed', '$rootScope', '$q'
(Request, FeedLoading, AutoPageLoading, NewLoading, Config, ActiveFeed,
$rootScope, $q) ->

	class Persistence

		constructor: (@_request, @_feedLoading, @_autoPageLoading, @_newLoading,
		              @_config, @_activeFeed, @_$rootScope) ->
			@_preventUselessAutoPageRequest = false
			@_lastFeedChange = new Date().getTime()

		init: ->
			###
			Loads the initial data from the server
			###

			@deferred = $q.defer()


			@getAllFolders()

			successCallback = =>
				@deferred.resolve()

			@getAllFeeds(successCallback)
			@userSettingsRead()
			@userSettingsLanguage()
			@userSettingsIsCompact()

			# items can only be loaded after the active feed is known
			@getActiveFeed =>
				@getItems(@_activeFeed.getType(), @_activeFeed.getId())
				
			@deferred.promise

		###
			ITEM CONTROLLER
		###
		getItems: (type, id, offset=0, onSuccess=null) ->
			onSuccess or= ->

			# show different loading signs
			if offset == 0
				loading = @_feedLoading
				# every change of the feed should inevitably reset the
				# autopage prevention
				@_lastFeedChange = new Date().getTime()
				@_preventUselessAutoPageRequest = false
				console.log 'free'
			else
				loading = @_autoPageLoading

			# loading sign handling
			loading.increase()


			successCallbackWrapper = ->
			lastChange = @_lastFeedChange
			# back up last change value in closure so we can compare it properly
			do (lastChange, offset, loading) =>
				successCallbackWrapper = (data) =>
					if data.data.items.length == 0 &&
					lastChange == @_lastFeedChange &&
					offset != 0
						@_preventUselessAutoPageRequest = true
						console.log 'lock'
					onSuccess(data)
					loading.decrease()
			failureCallbackWrapper = (data) ->
				loading.decrease()

			params =
				data:
					limit: @_config.itemBatchSize
					offset: offset
					id: id
					type: type
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			if not @_preventUselessAutoPageRequest
				@_request.get 'news_items', params


		getNewItems: (type, id, lastModified, onSuccess) ->
			onSuccess or= ->
			params =
				data:
					type: type
					id: id
					lastModified: lastModified
				onSuccess: onSuccess
				onFailure: onSuccess

			@_request.get 'news_items_new', params


		starItem: (feedId, guidHash) ->
			###
			Stars an item
			###
			params =
				routeParams:
					feedId: feedId
					guidHash: guidHash

			@_request.post 'news_items_star', params


		unstarItem: (feedId, guidHash) ->
			###
			Unstars an item
			###
			params =
				routeParams:
					feedId: feedId
					guidHash: guidHash

			@_request.post 'news_items_unstar', params


		readItem: (itemId) ->
			###
			Sets an item as read
			###
			params =
				routeParams:
					itemId: itemId

			@_request.post 'news_items_read', params



		unreadItem: (itemId) ->
			###
			Sets an item as unread
			###
			params =
				routeParams:
					itemId: itemId

			@_request.post 'news_items_unread', params


		setAllRead: (highestItemId) ->
			###
			sets all items as read
			###
			params =
				data:
					highestItemId: highestItemId

			@_request.post 'news_items_all_read', params


		###
			FEED CONTROLLER
		###
		getAllFeeds: (onSuccess, showLoading=true) ->
			onSuccess or= ->

			# loading sign handling
			if showLoading
				@_feedLoading.increase()
				successCallbackWrapper = (data) =>
					onSuccess()
					@_feedLoading.decrease()
				failureCallbackWrapper = (data) =>
					@_feedLoading.decrease()
			else
				successCallbackWrapper = (data) ->
					onSuccess()
				failureCallbackWrapper = (data) ->

			params =
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			@_request.get 'news_feeds', params


		getActiveFeed: (onSuccess) ->
			# loading sign handling
			@_feedLoading.increase()
			successCallbackWrapper = (data) =>
				onSuccess()
				@_feedLoading.decrease()
			failureCallbackWrapper = (data) =>
				@_feedLoading.decrease()

			params =
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			@_request.get 'news_feeds_active', params


		createFeed: (url, parentFolderId, onSuccess=null, onFailure=null) ->
			onSuccess or= ->
			onFailure or= ->
			params =
				data:
					parentFolderId: parentFolderId
					url: url
				onSuccess: onSuccess
				onFailure: onFailure

			@_request.post 'news_feeds_create', params


		deleteFeed: (feedId) ->
			params =
				routeParams:
					feedId: feedId

			@_request.delete 'news_feeds_delete', params


		restoreFeed: (feedId, onSuccess=null) ->
			onSuccess or= ->
			params =
				onSuccess: onSuccess
				routeParams:
					feedId: feedId

			@_request.post 'news_feeds_restore', params


		moveFeed: (feedId, folderId) ->
			###
			moves a feed to a new folder
			###
			params =
				routeParams:
					feedId: feedId
				data:
					parentFolderId: folderId

			@_request.post 'news_feeds_move', params


		renameFeed: (feedId, feedTitle) ->
			###
			rename a feed
			###
			params =
				routeParams:
					feedId: feedId
				data:
					feedTitle: feedTitle

			@_request.post 'news_feeds_rename', params


		setFeedRead: (feedId, highestItemId) ->
			###
			sets all items of a feed as read
			###
			params =
				routeParams:
					feedId: feedId
				data:
					highestItemId: highestItemId

			@_request.post 'news_feeds_read', params


		updateFeed: (feedId) ->
			###
			moves a feed to a new folder
			###
			params =
				routeParams:
					feedId: feedId

			@_request.post 'news_feeds_update', params


		importArticles: (json, onSuccess) ->
			params =
				data:
					json: json
				onSuccess: =>
					@getAllFeeds()
					onSuccess()

			@_request.post 'news_feeds_import_articles', params


		###
			FOLDER CONTROLLER
		###
		getAllFolders: (onSuccess, showLoading=true) ->
			onSuccess or= ->


			# loading sign handling
			if showLoading
				@_feedLoading.increase()
				successCallbackWrapper = (data) =>
					onSuccess()
					@_feedLoading.decrease()
				failureCallbackWrapper = (data) =>
					@_feedLoading.decrease()
			else
				successCallbackWrapper = (data) ->
					onSuccess()
				failureCallbackWrapper = (data) ->
			

			params =
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			@_request.get 'news_folders', params

	
		openFolder: (folderId) ->
			###
			Save if a folder was opened
			###
			params =
				routeParams:
					folderId: folderId

			@_request.post 'news_folders_open', params


		collapseFolder: (folderId) ->
			###
			Save if a folder was collapsed
			###
			params =
				routeParams:
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
				routeParams:
					folderId: folderId

			@_request.delete 'news_folders_delete', params


		restoreFolder: (folderId, onSuccess=null) ->
			onSuccess or= ->
			params =
				onSuccess: onSuccess
				routeParams:
					folderId: folderId

			@_request.post 'news_folders_restore', params


		renameFolder: (folderId, folderName) ->
			###
			Save if a folder was collapsed
			###
			params =
				routeParams:
					folderId: folderId
				data:
					folderName: folderName

			@_request.post 'news_folders_rename', params


		setFolderRead: (folderId, highestItemId) ->
			###
			sets all items of a folder as read
			###
			params =
				routeParams:
					folderId: folderId
				data:
					highestItemId: highestItemId

			@_request.post 'news_folders_read', params



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
		userSettingsRead: (onSuccess=null) ->
			###
			Gets the configs for read settings
			###
			onSuccess or= ->

			# loading sign handling
			@_feedLoading.increase()
			successCallbackWrapper = (data) =>
				onSuccess()
				@_feedLoading.decrease()
			failureCallbackWrapper = (data) =>
				@_feedLoading.decrease()
			
			params =
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			@_request.get 'news_usersettings_read', params


		userSettingsReadShow: (callback) ->
			###
			Sets the reader mode to show all
			###
			data =
				onSuccess: callback
			@_request.post 'news_usersettings_read_show', data


		userSettingsReadHide: (callback) ->
			###
			Sets the reader mode to show only unread
			###
			data =
				onSuccess: callback
			@_request.post 'news_usersettings_read_hide', data


		userSettingsLanguage: (onSuccess=null) ->
			onSuccess or= ->

			# loading sign handling
			@_feedLoading.increase()
			successCallbackWrapper = (data) =>
				onSuccess()
				@_feedLoading.decrease()
			failureCallbackWrapper = (data) =>
				@_feedLoading.decrease()

			data =
				onSuccess: successCallbackWrapper
				onFailure: failureCallbackWrapper

			@_request.get 'news_usersettings_language', data


		userSettingsIsCompact: ->
			@_request.get 'news_usersettings_iscompact'


		userSettingsSetCompact: (isCompact) ->
			###
			sets all items of a folder as read
			###
			params =
				data:
					compact: isCompact

			@_request.post 'news_usersettings_setcompact', params


		_triggerHideRead: ->
			@_$rootScope.$broadcast('triggerHideRead')


	return new Persistence(Request, FeedLoading, AutoPageLoading, NewLoading,
	                       Config, ActiveFeed, $rootScope)

]

