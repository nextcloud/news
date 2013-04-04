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


angular.module('News').factory 'SubscriptionsBl',
['_Bl', 'FeedBl', 'Persistence', 'ShowAll', 'ActiveFeed', 'FeedType',
'ItemModel',
(_Bl, FeedBl, Persistence, ShowAll, ActiveFeed, FeedType, ItemModel) ->

	class SubscriptionsBl extends _Bl

		constructor: (@_feedBl, @_showAll, feedType,
			persistence, activeFeed, itemModel) ->
			super(activeFeed, persistence, itemModel, feedType.Subscriptions)

		isVisible: ->
			if @isActive(0)
				return true

			if @_showAll.getShowAll()
				return @_feedBl.getNumberOfFeeds() > 0
			else
				visible = @_feedBl.getNumberOfFeeds() > 0 &&
				@_feedBl.getAllUnreadCount() > 0
				return visible


		markAllRead: ->
			@_feedBl.markAllRead()


		getUnreadCount: ->
			return @_feedBl.getAllUnreadCount()


	return new SubscriptionsBl(FeedBl, ShowAll, FeedType, Persistence,
	                           ActiveFeed, ItemModel)
]
