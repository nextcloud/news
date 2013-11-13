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


angular.module('News').factory 'FeedBusinessLayer',
['_BusinessLayer', 'ShowAll', 'Persistence', 'ActiveFeed', 'FeedType',
'ItemModel', 'FeedModel', 'NewLoading', '_ExistsError', 'Utils', '$rootScope',
'NewestItem',
(_BusinessLayer, ShowAll, Persistence, ActiveFeed, FeedType, ItemModel,
FeedModel, NewLoading, _ExistsError, Utils, $rootScope, NewestItem)->

	class FeedBusinessLayer extends _BusinessLayer

		constructor: (@_showAll, @_feedModel, persistence, activeFeed, feedType,
			          itemModel, @_newLoading, @_utils, $rootScope,
			          @_newestItem) ->
			super(activeFeed, persistence, itemModel, feedType.Feed, $rootScope)
			@_feedType = feedType


		getUnreadCount: (feedId) ->
			@_feedModel.getFeedUnreadCount(feedId)


		getFeedsOfFolder: (folderId) ->
			return @_feedModel.getAllOfFolder(folderId)


		getFolderUnreadCount: (folderId) ->
			@_feedModel.getFolderUnreadCount(folderId)


		getAllUnreadCount: ->
			return @_feedModel.getUnreadCount()


		delete: (feedId) ->
			feed = @_feedModel.removeById(feedId)

			data =
				undoCallback: =>
					@_persistence.restoreFeed feedId, =>
					@_persistence.getAllFeeds()
				caption: feed.title

			@_$rootScope.$broadcast 'undoMessage', data
			@_persistence.deleteFeed(feedId)


		renameFeedSet: (feedId) ->
                        feed = @_feedModel.getById(feedId)
                        feed.titleOrig = feed.title
                        feed.editing = true


		renameFeedUnset: (feedId) ->
                        feed = @_feedModel.getById(feedId)
                        feed.title = feed.titleOrig
                        feed.editing = false


		renameFeed: (feedId, feedTitle) ->
                        feed = @_feedModel.getById(feedId)
                        feed.editing = false
                        
                        if angular.isDefined(feed) and feedTitle != ""
                                @_persistence.renameFeed(feedId, feedTitle)

                        
		markRead: (feedId) ->
			feed = @_feedModel.getById(feedId)
			newestItemId = @_newestItem.getId()

			if angular.isDefined(feed) and newestItemId != 0
				feed.unreadCount = 0
				for item in @_itemModel.getAll()
					if item.feedId == feed.id
						item.setRead()
				@_persistence.setFeedRead(feedId, newestItemId)


		getNumberOfFeeds: ->
			return @_feedModel.size()

		noFeeds: ->
			return @getNumberOfFeeds() == 0

		
		isVisible: (feedId) ->
			if @isActive(feedId) or @_showAll.getShowAll()
				return true
			else
				return @_feedModel.getFeedUnreadCount(feedId) > 0


		move: (feedId, folderId) ->
			feed = @_feedModel.getById(feedId)
			if angular.isDefined(feed) and feed.folderId != folderId
				@_feedModel.update({
					id: feedId,
					folderId: folderId,
					url: feed.url})
				@_persistence.moveFeed(feedId, folderId)


		setShowAll: (showAll) ->
			@_showAll.setShowAll(showAll)

			# TODO: this callback is not tested with a unittest
			callback = =>
				@_itemModel.clear()
				@_newLoading.increase()
				@_persistence.getItems(
					@_activeFeed.getType(),
					@_activeFeed.getId(),
					0,
					=>
						@_newLoading.decrease()
				)
			if showAll
				@_persistence.userSettingsReadShow(callback)
			else
				@_persistence.userSettingsReadHide(callback)


		isShowAll: ->
			return @_showAll.getShowAll()


		getAll: ->
			return @_feedModel.getAll()


		getFeedLink: (feedId) ->
			feed = @_feedModel.getById(feedId)
			if angular.isDefined(feed)
				return feed.link


		create: (url, parentId=0, onSuccess=null, onFailure=null) ->
			onSuccess or= ->
			onFailure or= ->
			parentId = parseInt(parentId, 10)

			if angular.isUndefined(url) or url.trim() == ''
				throw new Error('Url must not be empty')
			
			url = url.trim()
			if url.indexOf('http') != 0
				url = 'http://' + url
			
			if @_feedModel.getByUrl(url)
				throw new _ExistsError('Exists already')

			feed =
				title: url
				url: url
				folderId: parentId
				unreadCount: 0
				faviconLink: 'url('+@_utils.imagePath('core', 'loading.gif')+')'

			@_feedModel.add(feed)

			success = (response) =>
				if response.status == 'error'
					feed.error = response.msg
					onFailure()
				else
					onSuccess(response.data)

			@_persistence.createFeed(url, parentId, success)


		markErrorRead: (url) ->
			@_feedModel.removeByUrl(url)


		importArticles: (json, callback) ->
			onSuccess = (response) =>
				callback()

			@_persistence.importArticles(json, onSuccess)


	return new FeedBusinessLayer(ShowAll, FeedModel, Persistence, ActiveFeed,
	                             FeedType, ItemModel, NewLoading, Utils,
	                             $rootScope, NewestItem)

]