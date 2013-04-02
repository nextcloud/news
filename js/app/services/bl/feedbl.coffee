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


angular.module('News').factory '_FeedBl', ->

	class FeedBl

		constructor: (@_feedModel, @_itemBl, @_persistence) ->


		getFeedUnreadCount: (feedId) ->
			@_feedModel.getFeedUnreadCount(feedId)


		getFeedsOfFolder: (folderId) ->
			return @_feedModel.getAllOfFolder(folderId)


		getFolderUnreadCount: (folderId) ->
			@_feedModel.getFolderUnreadCount(folderId)


		getUnreadCount: ->
			return @_feedModel.getUnreadCount()


		delete: (feedId) ->
			@_feedModel.removeById(feedId)
			@_persistence.deleteFeed(feedId)


		markFeedRead: (feedId) ->
			feed = @_feedModel.getById(feedId)
			if angular.isDefined(feed)
				feed.unreadCount = 0
				@_itemBl.markAllRead(feedId)


		markAllRead: ->
			for feed in @_feedModel.getAll()
				@markFeedRead(feed.id)



	return FeedBl
