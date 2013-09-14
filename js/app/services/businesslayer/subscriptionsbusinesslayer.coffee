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


angular.module('News').factory 'SubscriptionsBusinessLayer',
['_BusinessLayer', 'FeedBusinessLayer', 'Persistence', 'ShowAll', 'ActiveFeed',
'FeedType', 'ItemModel', 'FeedModel', 'NewestItem', '$rootScope',
(_BusinessLayer, FeedBusinessLayer, Persistence, ShowAll, ActiveFeed, FeedType,
ItemModel, FeedModel, NewestItem, $rootScope) ->

	class SubscriptionsBusinessLayer extends _BusinessLayer

		constructor: (@_feedBusinessLayer, @_showAll, feedType,
			persistence, activeFeed, itemModel, @_feedModel, @_newestItem,
			$rootScope) ->
			super(activeFeed, persistence, itemModel, feedType.Subscriptions,
				$rootScope)

		isVisible: ->
			if @isActive(0) and @_feedBusinessLayer.getNumberOfFeeds() > 0
				return true

			if @_showAll.getShowAll()
				return @_feedBusinessLayer.getNumberOfFeeds() > 0
			else
				visible = @_feedBusinessLayer.getNumberOfFeeds() > 0 &&
				@_feedBusinessLayer.getAllUnreadCount() > 0
				return visible


		markRead: ->
			newestItemId = @_newestItem.getId()

			if newestItemId != 0
				for feed in @_feedModel.getAll()
					feed.unreadCount = 0
				for item in @_itemModel.getAll()
					item.setRead()
				@_persistence.setAllRead(newestItemId)


		getUnreadCount: ->
			return @_feedBusinessLayer.getAllUnreadCount()


	return new SubscriptionsBusinessLayer(FeedBusinessLayer, ShowAll, FeedType,
	                                      Persistence, ActiveFeed, ItemModel,
	                                      FeedModel, NewestItem, $rootScope)
]
