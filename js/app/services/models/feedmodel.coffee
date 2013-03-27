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


angular.module('News').factory '_FeedModel',
['_Model', '_EqualQuery',
(_Model, _EqualQuery) ->

	class FeedModel extends _Model

		constructor: (@_utils) ->
			@_urlHash = {}
			super()


		clear: ->
			@_urlHash = {}
			super()


		add: (item, clearCache=true) ->
			if item.faviconLink == null
				item.faviconLink = 'url(' +
					@_utils.imagePath('news', 'rss.svg') + ')'

			# since the feedurl is also unique, we want to update items that have
			# the same url
			entry = @_urlHash[item.urlHash]
			if angular.isDefined(entry)
				@update(item, clearCache)
			else
				@_urlHash[item.urlHash] = item
				super(item, clearCache)


		update: (item, clearCache=true) ->
			entry = @_urlHash[item.urlHash]

			# first update id that could have changed
			delete @_dataMap[entry.id]
			@_dataMap[item.id] = entry

			# now copy over the elements data attrs
			for key, value of item
				if key == 'urlHash'
					continue
				else
					entry[key] = value



		removeById: (id) ->
			item = @getById(id)
			delete @_urlHash[item.urlHash]
			super(id)


		getByUrlHash: (urlHash) ->
			return @_urlHash[urlHash]


		getUnreadCount: ->
			count = 0
			for feed in @getAll()
				count += feed.unreadCount

			return count


		getFeedUnreadCount: (feedId) ->
			feed = @getById(feedId)
			count = 0
			if angular.isDefined(feed)
				return count += feed.unreadCount
			else
				return 0


		getFolderUnreadCount: (folderId) ->
			query = new _EqualQuery('folderId', folderId)
			count = 0
			for feed in @get(query)
				count += feed.unreadCount

			return count


		getAllOfFolder: (folderId) ->
			query = new _EqualQuery('folderId', folderId)
			return @get(query)


	return FeedModel
]