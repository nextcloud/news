###
# ownCloud - News app
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

angular.module('News').factory '_PersistenceNews', ['Persistence', (Persistence) ->

	class PersistenceNews extends Persistence

		constructor: ($http, @$rootScope, @loading, @publisher) ->
			super('news', $http)


		updateModels: (data) ->
			for type, value of data
				@publisher.publish(type, value)


		loadInitial: () ->
			@loading.loading += 1
			OC.Router.registerLoadedCallback =>
				@post 'init', {}, (json) =>
					@loading.loading -= 1
					@updateModels(json.data)
					@$rootScope.$broadcast('triggerHideRead')
					@setInitialized(true)
				, null, true


		loadFeed: (type, id, latestFeedId, latestTimestamp, limit=20) ->
			data = 
				type: type
				id: id
				latestFeedId: latestFeedId
				latestTimestamp: latestTimestamp
				limit: limit

			@loading.loading += 1
			@post 'loadfeed', data, (json) =>
				@loading.loading -= 1
				@updateModels(json.data)


		createFeed: (feedUrl, folderId, onSuccess, onError) ->
			data = 
				feedUrl: feedUrl
				folderId: folderId
			@post 'createfeed', data, (json) =>
				onSuccess(json.data)
				@updateModels(json.data)
			, onError


		deleteFeed: (feedId, onSuccess) ->
			data = 
				feedId: feedId
			@post 'deletefeed', data, onSuccess


		moveFeedToFolder: (feedId, folderId) ->
			data =
				feedId: feedId
				folderId: folderId
			@post 'movefeedtofolder', data


		createFolder: (folderName, onSuccess) ->
			data = 
				folderName: folderName
			@post 'createfolder', data, (json) =>
				onSuccess(json.data)
				@updateModels(json.data)


		deleteFolder: (folderId) ->
			data = 
				folderId: folderId
			@post 'deletefolder', data


		changeFolderName: (folderId, newFolderName) ->
			data = 
				folderId: folderId
				newFolderName: newFolderName
			@post 'folderName', data


		showAll: (isShowAll) ->
			data = 
				showAll: isShowAll
			@post 'setshowall', data


		markRead: (itemId, isRead) ->
			if isRead
				status = 'read'
			else
				status = 'unread'

			data =
				itemId: itemId
				status: status

			@post 'setitemstatus', data


		setImportant: (itemId, isImportant) ->
			if isImportant
				status = 'important'
			else
				status = 'unimportant'

			data =
				itemId: itemId
				status: status

			@post 'setitemstatus', data


		collapseFolder: (folderId, value) ->
			data =
				folderId: folderId
				opened: value
			@post 'collapsefolder', data


		updateFeed: (feedId) ->
			data =
				feedId: feedId

			@post 'updatefeed', data, (json) =>	
				@updateModels(json.data)			


		setAllItemsRead: (feedId, mostRecentItemId) ->
			data =
				feedId: feedId
				mostRecentItemId: mostRecentItemId
			@post 'setallitemsread', data



	return PersistenceNews
]