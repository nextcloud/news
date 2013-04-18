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

angular.module('News').factory 'FeedModel',
['_Model', '_EqualQuery', 'Utils',
(_Model, _EqualQuery, Utils) ->

	class FeedModel extends _Model

		constructor: (@_utils) ->
			@_url = {}
			super()


		clear: ->
			@_url = {}
			super()


		add: (data, clearCache=true) ->
			if data.faviconLink == null
				data.faviconLink = 'url(' +
					@_utils.imagePath('news', 'rss.svg') + ')'
			else if angular.isDefined(data.faviconLink)
				data.faviconLink = 'url(' + data.faviconLink + ')'
			###
			We want to add a feed on the client side before
			we have an id from the server. Once the server returns
			an id, we have to update the existing item without id
			###

			item = @_url[data.url]

			# update in the following cases:
			# * the id is defined and the item exists
			# * the id is not defined and the name exists in the cache
			updateById = angular.isDefined(data.id) and
			angular.isDefined(@getById(data.id))
			
			updateByUrl = angular.isDefined(item) and
			angular.isUndefined(item.id)
			
			if updateById or updateByUrl
				@update(data, clearCache)
			else
				if angular.isDefined(data.url)
					# if the item is not yet in the name cache it must be added
					@_url[data.url] = data
					
					# in case there is an id it can go through the normal add method
					if angular.isDefined(data.id)
						super(data, clearCache)

					# if there is no id we just want it to appear in the list
					else
						@_data.push(data)
						if clearCache
							@_invalidateCache()


		update: (data, clearCache=true) ->
			# only when the id on the updated item does not exist we wish
			# to update by name, otherwise we always update by id
			if angular.isDefined(data.url)
				item = @_url[data.url]

			# update by name
			if angular.isUndefined(data.id)	and angular.isDefined(item)
				angular.extend(item, data)
			
			else
				# this case happens when there exists an element with the same
				# name but it has no id yet
				if angular.isDefined(data.id) and
				angular.isDefined(item) and
				angular.isUndefined(item.id)
					item.id = data.id
					@_dataMap[data.id] = item

				# if an update comes in and we update because of the id
				# we need to fix the name cache if the name was changed
				itemWithId = @getById(data.id)
				if angular.isDefined(itemWithId) and
				itemWithId.url != data.url
					delete @_url[itemWithId.url]
					@_url[data.url] = itemWithId

				super(data, clearCache)


		removeById: (id) ->
			item = @getById(id)
			delete @_url[item.url]
			super(id)


		getByUrl: (url) ->
			return @_url[url]


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
			query = new _EqualQuery('folderId', parseInt(folderId))
			count = 0
			for feed in @get(query)
				count += feed.unreadCount

			return count


		getAllOfFolder: (folderId) ->
			query = new _EqualQuery('folderId', parseInt(folderId))
			return @get(query)


		removeByUrl: (url, clearCache=true) ->
			###
			Remove an entry by id
			###

			# remove from data map
			for key, value of @_dataMap
				if @_dataMap[key].url == url
					delete @_dataMap[key]
					break

			for entry, counter in @_data
				if entry.url == url
					@_data.splice(counter, 1)
					delete @_url[url]

					if clearCache
						@_invalidateCache()
					break


	return new FeedModel(Utils)
]