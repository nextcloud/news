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


angular.module('News').factory 'StarredBusinessLayer',
['_BusinessLayer', 'StarredCount', 'Persistence', 'ActiveFeed', 'FeedType',
'ItemModel', '$rootScope',
(_BusinessLayer, StarredCount, Persistence, ActiveFeed, FeedType, ItemModel,
$rootScope) ->

	class StarredBusinessLayer extends _BusinessLayer

		constructor: (@_starredCount, feedType,
			persistence, activeFeed, itemModel, $rootScope) ->
			super(activeFeed, persistence, itemModel, feedType.Starred, $rootScope)

		isVisible: ->
			if @isActive(0)
				return true
			else
				return @_starredCount.getStarredCount() > 0


		getUnreadCount: ->
			return @_starredCount.getStarredCount()


		increaseCount: ->
			@_starredCount.setStarredCount(@_starredCount.getStarredCount() + 1)


		decreaseCount: ->
			@_starredCount.setStarredCount(@_starredCount.getStarredCount() - 1)

	return new StarredBusinessLayer(StarredCount, FeedType, Persistence,
	                     ActiveFeed, ItemModel, $rootScope)
]
