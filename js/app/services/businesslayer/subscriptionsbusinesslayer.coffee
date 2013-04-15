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


angular.module('News').factory 'SubscriptionsBusinessLayer',
['_BusinessLayer', 'FeedBusinessLayer', 'Persistence', 'ShowAll', 'ActiveFeed',
'FeedType', 'ItemModel',
(_BusinessLayer, FeedBusinessLayer, Persistence, ShowAll, ActiveFeed, FeedType,
ItemModel) ->

	class SubscriptionsBusinessLayer extends _BusinessLayer

		constructor: (@_feedBusinessLayer, @_showAll, feedType,
			persistence, activeFeed, itemModel) ->
			super(activeFeed, persistence, itemModel, feedType.Subscriptions)

		isVisible: ->
			if @isActive(0)
				return true

			if @_showAll.getShowAll()
				return @_feedBusinessLayer.getNumberOfFeeds() > 0
			else
				visible = @_feedBusinessLayer.getNumberOfFeeds() > 0 &&
				@_feedBusinessLayer.getAllUnreadCount() > 0
				return visible


		markAllRead: ->
			@_feedBusinessLayer.markAllRead()


		getUnreadCount: ->
			return @_feedBusinessLayer.getAllUnreadCount()


	return new SubscriptionsBusinessLayer(FeedBusinessLayer, ShowAll, FeedType,
	                                      Persistence, ActiveFeed, ItemModel)
]
