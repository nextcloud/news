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
					@_showAll, @_feedType, @_starredCount, @_persistence,
					@_folderBl, @_feedBl) ->

			@_isAddingFolder = false
			@_isAddingFeed = false

			# bind internal stuff to scope
			@$scope.feeds = @_feedModel.getAll()
			@$scope.folders = @_folderModel.getAll()
			@$scope.feedType = @_feedType
			@$scope.folderBl = @_folderBl
			@$scope.feedBl = @_feedBl

			
			@$scope.isShown = (type, id) =>
				return @isShown(type, id)

			@$scope.getUnreadCount = =>
				return @_transFormCount(@_feedBl.getUnreadCount())

			@$scope.getStarredCount = =>
				return @_transFormCount(@_starredCount.getStarredCount())

			@$scope.getFeedUnreadCount = (feedId) =>
				return @_transFormCount(@_feedBl.getFeedUnreadCount(feedId))

			@$scope.getUnreadCount = (folderId) =>
				return @_transFormCount(@_folderBl.getFolderUnreadCount(folderId))

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

			@$scope.isAddingFolder = =>
				return @_isAddingFolder

			@$scope.isAddingFeed = =>
				return @_isAddingFeed

			@$scope.toggleFolder = (folderId) =>
				@toggleFolder(folderId)

			@$scope.addFeed = (feedUrl, parentFolderId=0) =>
				@$scope.feedEmptyError = false
				@$scope.feedError = false
				
				if angular.isUndefined(feedUrl) or feedUrl.trim() == ''
					@$scope.feedEmptyError = true
				
				if not @$scope.feedEmptyError
					@_isAddingFeed = true

					onError = =>
						@$scope.feedError = true
						@_isAddingFeed = false

					onSuccess = (data) =>
						if data.status == 'error'
							onError()
						else
							@$scope.feedUrl = ''
							@_isAddingFeed = false
					
					@_persistence.createFeed(feedUrl.trim(), parentFolderId,
						onSuccess, onError)
				

			@$scope.addFolder = (folderName) =>
				@$scope.folderEmptyError = false
				@$scope.folderExistsError = false

				if angular.isUndefined(folderName) or folderName.trim() == ''
					@$scope.folderEmptyError = true
				else
					folderName = folderName.trim()
					if @_folderModel.nameExists(folderName)
						@$scope.folderExistsError = true

				if not (@$scope.folderEmptyError or @$scope.folderExistsError)
					@_isAddingFolder = true
					@_persistence.createFolder folderName, 0, =>
						@$scope.folderName = ''
						@$scope.addNewFolder = false
						@_isAddingFolder = false


		isFeedActive: (type, id) ->
			return type == @_active.getType() and id == @_active.getId()


		isShown: (type, id) ->
			hasUnread = @getUnreadCount(type, id) > 0
			if hasUnread
				return true
			else
				if @isShowAll()
					switch type
						when @_feedType.Subscriptions
							return @_feedModel.size() > 0
						when @_feedType.Folder
							return @_folderModel.size() > 0
						when @_feedType.Feed
							return @_feedModel.size() > 0
			return false
					

		isShowAll: ->
			return @_showAll.getShowAll()


		_transFormCount: (count) ->
			if count > 999
				count = '999+'

			return count


		loadFeed: (type, id) ->
			# TODO: use polymorphism instead of switches
			if type != @_active.getType() or id != @_active.getId()
				@_itemModel.clear()
				@_persistence.getItems(type, id, 0)
				@_active.handle({id: id, type: type})
			else
				lastModified = @_itemModel.getHighestId()
				@_persistence.getItems(type, id, 0, null, lastModified)


		setShowAll: (showAll) ->
			@_showAll.setShowAll(showAll)
			if showAll
				@_persistence.userSettingsReadShow()
			else
				@_persistence.userSettingsReadHide()


	return FeedController