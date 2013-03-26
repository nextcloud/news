###

ownCloud - News

@_author Bernhard Posselt
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


angular.module('News').factory '_ItemController', ->

	class ItemController

		constructor: (@_$scope, @_itemModel, @_feedModel, @_feedLoading) ->

			@_$scope.items = @_itemModel.getAll()

			@_$scope.isLoading = =>
				return @_feedLoading.isLoading()

			@_$scope.getFeedTitle = (feedId) =>
				feed = @_feedModel.getById(feedId)
				if angular.isDefined(feed)
					return feed.title
				else
					return ''


	return ItemController