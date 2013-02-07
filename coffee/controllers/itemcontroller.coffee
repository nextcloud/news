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

angular.module('News').factory '_ItemController', ['Controller', (Controller) ->

	class ItemController extends Controller

		constructor: (@$scope, @itemModel, @activeFeed, @persistence, @feedModel,
						@starredCount, @garbageRegistry, @showAll, @loading
						@$rootScope, @feedType) ->

			@batchSize = 4
			@loaderQueue = 0
			
			@$scope.getItems = (type, id) =>
				return @itemModel.getItemsByTypeAndId(type, id)

			@$scope.items = @itemModel.getItems()
			@$scope.loading = @loading


			@$scope.scroll = =>

			@$scope.activeFeed = @activeFeed

			@$scope.$on 'read', (scope, params) =>
				@$scope.markRead(params.id, params.feed)


			@$scope.loadFeed = (feedId) =>
				params =
					id: feedId
					type: @feedType.Feed
				@$rootScope.$broadcast 'loadFeed', params


			@$scope.markRead = (itemId, feedId) =>
				item = @itemModel.getItemById(itemId)
				feed = @feedModel.getItemById(feedId)
				
				if not item.keptUnread && !item.isRead
					item.isRead = true
					feed.unreadCount -= 1

					# this item will be completely deleted if showAll is false
					if not @showAll.showAll
						@garbageRegistry.register(item)

					@persistence.markRead(itemId, true)


			@$scope.keepUnread = (itemId, feedId) =>
				item = @itemModel.getItemById(itemId)
				feed = @feedModel.getItemById(feedId)

				
				item.keptUnread = !item.keptUnread

				if item.isRead
					item.isRead = false
					feed.unreadCount += 1

					@persistence.markRead(itemId, false)


			@$scope.isKeptUnread = (itemId) =>
				return @itemModel.getItemById(itemId).keptUnread


			@$scope.toggleImportant = (itemId) =>
				item = @itemModel.getItemById(itemId)
				
				# cache
				@itemModel.setImportant(itemId, !item.isImportant)

				if item.isImportant
					@starredCount.count += 1
				else
					@starredCount.count -= 1

				@persistence.setImportant(itemId, item.isImportant)


	return ItemController
]