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


angular.module('News').factory 'FeedBl',
['_Bl', 'ShowAll', 'Persistence', 'ActiveFeed', 'FeedType', 'ItemModel',
'FeedModel', 'NewLoading', '_ExistsError',
(_Bl, ShowAll, Persistence, ActiveFeed, FeedType, ItemModel, FeedModel,
NewLoading, _ExistsError) ->

	class FeedBl extends _Bl

		constructor: (@_showAll, @_feedModel, persistence, activeFeed, feedType,
			          itemModel, @_newLoading) ->
			super(activeFeed, persistence, itemModel, feedType.Feed)


		getUnreadCount: (feedId) ->
			@_feedModel.getFeedUnreadCount(feedId)


		getFeedsOfFolder: (folderId) ->
			return @_feedModel.getAllOfFolder(folderId)


		getFolderUnreadCount: (folderId) ->
			@_feedModel.getFolderUnreadCount(folderId)


		getAllUnreadCount: ->
			return @_feedModel.getUnreadCount()


		delete: (feedId) ->
			@_feedModel.removeById(feedId)
			@_persistence.deleteFeed(feedId)


		markFeedRead: (feedId) ->
			feed = @_feedModel.getById(feedId)
			if angular.isDefined(feed)
				feed.unreadCount = 0
				highestItemId = @_itemModel.getHighestId()
				@_persistence.setFeedRead(feedId, highestItemId)
				for item in @_itemModel.getAll()
					item.setRead()


		markAllRead: ->
			for feed in @_feedModel.getAll()
				@markFeedRead(feed.id)


		getNumberOfFeeds: ->
			return @_feedModel.size()

		
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
					urlHash: feed.urlHash})
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

			if angular.isUndefined(url) or url.trim() == ''
				throw new Error()
			
			url = url.trim()
			urlHash = hex_md5(url)
			
			if @_feedModel.getByUrlHash(urlHash)
				throw new _ExistsError()

			feed =
				title: url.replace(
					/^(?:https?:\/\/)?(?:www\.)?([a-z0-9_\-\.]+)(?:\/.*)?$/gi,
					'$1')
				url: url
				urlHash: urlHash
				folderId: parentId

			@_feedModel.add(feed)

			success = (response) =>
				if response.status == 'error'
					feed.error = response.msg
					onFailure()
				else
					onSuccess(response.data)

			@_persistence.createFeed url, parentId, success


	return new FeedBl(ShowAll, FeedModel, Persistence, ActiveFeed, FeedType,
	                  ItemModel, NewLoading)

]