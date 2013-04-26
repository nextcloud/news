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


angular.module('News').factory 'ItemBusinessLayer',
['ItemModel', 'FeedModel', 'Persistence', 'ActiveFeed', 'FeedType',
'StarredBusinessLayer', 'NewestItem',
(ItemModel, FeedModel, Persistence, ActiveFeed, FeedType,
StarredBusinessLayer, NewestItem) ->

	class ItemBusinessLayer

		constructor: (@_itemModel, @_feedModel, @_persistence, @_activeFeed,
		              @_feedType, @_starredBusinessLayer, @_newestItem) ->

		getAll: ->
			return @_itemModel.getAll()


		noFeedActive: ->
			return @_activeFeed.getType() != @_feedType.Feed


		isKeptUnread: (itemId) ->
			item = @_itemModel.getById(itemId)
			if angular.isDefined(item) and angular.isDefined(item.keptUnread)
				return item.keptUnread
			return false


		toggleKeepUnread: (itemId) ->
			item = @_itemModel.getById(itemId)
			if angular.isDefined(item) and not item.keptUnread
				item.keptUnread = true
				if item.isRead()
					@setUnread(itemId)
			else
				item.keptUnread = false


		toggleStarred: (itemId) ->
			item = @_itemModel.getById(itemId)
			if item.isStarred()
				item.setUnstarred()
				@_starredBusinessLayer.decreaseCount()
				@_persistence.unstarItem(item.feedId, item.guidHash)
			else
				item.setStarred()
				@_starredBusinessLayer.increaseCount()
				@_persistence.starItem(item.feedId, item.guidHash)


		setRead: (itemId) ->
			item = @_itemModel.getById(itemId)
			if angular.isDefined(item)
				
				keptUnread = angular.isDefined(item.keptUnread) and
				item.keptUnread
				
				if not (item.isRead() or keptUnread)
					item.setRead()
					@_persistence.readItem(itemId)

					feed = @_feedModel.getById(item.feedId)
					if angular.isDefined(feed)
						feed.unreadCount -= 1


		setUnread: (itemId) ->
			item = @_itemModel.getById(itemId)
			if angular.isDefined(item)
				if item.isRead()
					item.setUnread()
					@_persistence.unreadItem(itemId)

					feed = @_feedModel.getById(item.feedId)
					if angular.isDefined(feed)
						feed.unreadCount += 1


		getFeedTitle: (itemId) ->
			item = @_itemModel.getById(itemId)
			if angular.isDefined(item)
				feed = @_feedModel.getById(item.feedId)
				if angular.isDefined(feed)
					return feed.title


		loadNext: (callback) ->
			size = @_itemModel.size()
			if size != 0
				@_persistence.getItems @_activeFeed.getType(),
				                       @_activeFeed.getId(),
				                       size,
				                       @_newestItem.getId(),
				                       callback
			else
				callback()



		loadNew: ->



	return new ItemBusinessLayer(ItemModel, FeedModel, Persistence, ActiveFeed,
	                             FeedType, StarredBusinessLayer, NewestItem)

]