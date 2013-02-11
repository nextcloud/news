###
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###
angular.module('News').factory '_FeedController', ['Controller', (Controller) ->

	class FeedController extends Controller

		constructor: (@$scope, @feedModel, @folderModel, @feedType, @activeFeed, 
					  @persistence, @starredCount, @showAll, @itemModel,
					  @garbageRegistry, @$rootScope, @loading, @config) ->

			@showSubscriptions = true

			@$scope.feeds = @feedModel.getItems()
			@$scope.folders = @folderModel.getItems()
			@$scope.feedType = @feedType

			@$scope.getShowAll = =>
				return @showAll.showAll

			@$scope.setShowAll = (value) =>
				@showAll.showAll = value
				@persistence.showAll(value)
				@$rootScope.$broadcast('triggerHideRead')

			@$scope.addFeed = (url, folder) =>
				@$scope.feedEmptyError = false
				@$scope.feedExistsError = false
				@$scope.feedError = false
			
				if url == undefined or url.trim() == ''
					@$scope.feedEmptyError = true
				else
					url = url.trim()
					for feed in @feedModel.getItems()
						if url == feed.url # FIXME: can we really compare this
							@$scope.feedExistsError = true

				if not (@$scope.feedEmptyError or @$scope.feedExistsError)
					if folder == undefined
						folderId = 0
					else
						folderId = folder.id
					@$scope.adding = true
					onSuccess = =>
						@$scope.feedUrl = ''
						@$scope.adding = false
					onError = =>
						@$scope.feedError = true
						@$scope.adding = false
					@persistence.createFeed(url, folderId, onSuccess, onError)


			@$scope.addFolder = (name) =>
				@$scope.folderEmptyError = false
				@$scope.folderExistsError = false

				if name == undefined or name.trim() == ''
					@$scope.folderEmptyError = true
				else
					name = name.trim()
					for folder in @folderModel.getItems()
						if name.toLowerCase() == folder.name.toLowerCase()
							@$scope.folderExistsError = true

				if not (@$scope.folderEmptyError or @$scope.folderExistsError)
					@addingFolder = true
					onSuccess = =>
						@$scope.folderName = ''
						@addingFolder = false
					@persistence.createFolder(name, onSuccess)

			@$scope.toggleFolder = (folderId) =>
				folder = @folderModel.getItemById(folderId)
				folder.open = !folder.open
				@persistence.collapseFolder(folder.id, folder.open)


			@$scope.isFeedActive = (type, id) =>
				if type == @activeFeed.type && id == @activeFeed.id
					return true
				else
					return false


			@$scope.loadFeed = (type, id) =>
				@loadFeed(type, id)


			@$scope.getUnreadCount = (type, id) =>
				count = @getUnreadCount(type, id)
				if count > 999
					return "999+"
				else 
					return count


			@$scope.renameFolder = ->
				alert 'not implemented yet, needs better solution'


			@$scope.triggerHideRead = =>
				@triggerHideRead()


			@$scope.isShown = (type, id) =>
				switch type
					when @feedType.Subscriptions then return @showSubscriptions
					when @feedType.Starred then return @starredCount.count > 0


			@$scope.delete = (type, id) =>
				switch type
					when @feedType.Folder
						@folderModel.removeById(id)
						@persistence.deleteFolder(id)

					when @feedType.Feed
						@feedModel.removeById(id)
						@persistence.deleteFeed(id)


			@$scope.markAllRead = (type, id) =>
				switch type
					when @feedType.Feed
						for itemId, item of @itemModel.getItemsByTypeAndId(type, id)
							item.isRead = true	
						feed = @feedModel.getItemById(id)
						feed.unreadCount = 0
						mostRecentItemId = @itemModel.getHighestId(type, id)
						@persistence.setAllItemsRead(feed.id, mostRecentItemId)
					
					when @feedType.Folder
						for itemId, item of @itemModel.getItemsByTypeAndId(type, id)
							item.isRead = true
						for feedId in @itemModel.getFeedsOfFolderId(id)
							feed = @feedModel.getItemById(feedId)
							feed.unreadCount = 0
							mostRecentItemId = @itemModel.getHighestId(type, feedId)
							@persistence.setAllItemsRead(feedId, mostRecentItemId)

					when @feedType.Subscriptions
						for itemId, item of @itemModel.getItemsByTypeAndId(type, id)
							item.isRead = true
						for feed in @feedModel.getItems()
							feed.unreadCount = 0
							mostRecentItemId = @itemModel.getHighestId(type, feed.id)
							@persistence.setAllItemsRead(feed.id, mostRecentItemId)

			@$scope.$on 'triggerHideRead', =>
				@itemModel.clearCache()
				@triggerHideRead()
				@loadFeed(activeFeed.type, activeFeed.id)

			@$scope.$on 'loadFeed', (scope, params) =>
				@loadFeed(params.type, params.id)

			@$scope.$on 'moveFeedToFolder', (scope, params) =>
				@moveFeedToFolder(params.feedId, params.folderId)

			setInterval =>
				@updateFeeds()
			, @config.FeedUpdateInterval



		updateFeeds: ->
			for feed in @feedModel.getItems()
				@persistence.updateFeed(feed.id)


		moveFeedToFolder: (feedId, folderId) ->
			feed = @feedModel.getItemById(feedId)
			if feed.folderId != folderId
				feed.folderId = folderId
				@feedModel.markAccessed()
				@persistence.moveFeedToFolder(feedId, folderId)


		loadFeed: (type, id) ->
			# to not go crazy with autopaging, clear the caches if we switch the
			# type of the feed. if the caches only contain seperate feeds, the
			# cache and autopage logic works fine. if the feed contains more than
			# one 
			if type != @activeFeed.type or id != @activeFeed.id
				if not (type == @feedType.Feed && @activeFeed.type == @feedType.Feed)
					@itemModel.clearCache()

			@activeFeed.id = id
			@activeFeed.type = type
			@$scope.triggerHideRead()
			@persistence.loadFeed(type, id, 
				@itemModel.getHighestId(type, id),
				@itemModel.getHighestTimestamp(type, id), @config.initialLoadedItemsNr)


		triggerHideRead: () ->
			preventParentFolder = 0

			# feeds
			for feed in @feedModel.getItems()
				if @showAll.showAll == false &&	@getUnreadCount(@feedType.Feed, feed.id) == 0

					# we dont hide the selected feed and folder. But we also dont hide
					# the parent folder of the selcted feed
					if @activeFeed.type == @feedType.Feed && @activeFeed.id == feed.id
						feed.show = true
						preventParentFolder = feed.folderId
					else
						feed.show = false
				else
					feed.show = true

			# folders
			for folder in @folderModel.getItems()
				if @showAll.showAll == false && @getUnreadCount(@feedType.Folder, folder.id) == 0
					# prevent hiding when childfeed is active
					if (@activeFeed.type == @feedType.Folder && @activeFeed.id == folder.id) ||	preventParentFolder == folder.id
						folder.show = true
					else
						folder.show = false
				else
					folder.show = true

			# subscriptions
			if @showAll.showAll == false && @getUnreadCount(@feedType.Subscriptions, 0) == 0
				if @activeFeed.type == @feedType.Subscriptions
					@showSubscriptions = true
				else
					@showSubscriptions = false
			else
				@showSubscriptions = true

			# starred
			if @showAll.showAll == false && @getUnreadCount(@feedType.Starred, 0) == 0
				if @activeFeed.type == @feedType.Starred
					@showStarred = true
				else
					@showStarred = false
			else
				@showStarred = true

			@garbageRegistry.clear()



		getUnreadCount: (type, id) ->
			switch type
				when @feedType.Feed 
					return @feedModel.getItemById(id).unreadCount

				when @feedType.Folder
					counter = 0
					for feed in @feedModel.getItems()
						if feed.folderId == id
							counter += feed.unreadCount
					return counter

				when @feedType.Starred
					return @starredCount.count

				when @feedType.Subscriptions
					counter = 0
					for feed in @feedModel.getItems()
						counter += feed.unreadCount
					return counter


	return FeedController
]