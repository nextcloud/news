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


angular.module('News').factory '_FeedController', ->

	class FeedController

		constructor: (@$scope, @_folderModel, @_feedModel, @_active, 
					@_showAll, @_feedType, @_starredCount) ->

			# bind internal stuff to scope
			@$scope.feeds = @_feedModel.getAll()
			@$scope.folders = @_folderModel.getAll()
			@$scope.feedType = @_feedType

			@$scope.isFeedActive = (type, id) =>
				return @isFeedActive(type, id)
			
			@$scope.isShown = (type, id) =>
				return @isShown(type, id)

			@$scope.getUnreadCount = (type, id) =>
				return @getUnreadCount(type, id)

			@$scope.isShowAll = =>
				return @isShowAll()

			@$scope.loadFeed = (type, id) =>
				@loadFeed(type, id)

			@$scope.hasFeeds = (folderId) =>
				return @hasFeeds(folderId)

			@$scope.delete = (type, id) =>
				@delete(type, id)

			@$scope.markAllRead = (type, id) =>
				@markAllRead(type, id)

			@$scope.getFeedsOfFolder = (folderId) =>
				return @getFeedsOfFolder(folderId)

			@$scope.setShowAll = (showAll) =>
				@setShowAll(showAll)


		isFeedActive: (type, id) ->
			return type == @_active.getType() and id = @_active.getId()


		isShown: (type, id) ->
			if @isShowAll()
				return true
			else
				return @getUnreadCount(type, id) > 0


		isShowAll: ->
			return @_showAll.getShowAll()


		getUnreadCount: (type, id) ->


		loadFeed: (type, id) ->


		hasFeeds: (folderId) ->


		delete: (type, id) ->


		markAllRead: (type, id) ->


		getFeedsOfFolder: (folderId) ->



	return FeedController