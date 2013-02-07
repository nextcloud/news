###
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###

angular.module('News').factory '_ItemModel', ['Model', (Model) ->

	class ItemModel extends Model

		constructor: (@cache, @feedType) ->
			super()			


		clearCache: () ->
			@cache.clear()
			super()


		add: (item) ->
			item = @bindAdditional(item)
			if super(item)
				@cache.add(@getItemById(item.id))			


		bindAdditional: (item) ->
			item.getRelativeDate = ->
				return moment.unix(this.date).fromNow();
			
			item.getAuthorLine = ->
				if this.author != null and this.author.trim() != ""
					return "by " + this.author
				else
					return ""
			return item


		removeById: (itemId) ->
			item = @getItemById(itemId)
			if item != undefined
				@cache.remove(item)
				super(itemId)

			
		getHighestId: (type, id) ->
			@cache.getHighestId(type, id)


		getHighestTimestamp: (type, id) ->
			@cache.getHighestTimestamp(type, id)			


		getLowestId: (type, id) ->
			@cache.getLowestId(type, id)
			

		getLowestTimestamp: (type, id) ->
			@cache.getLowestTimestamp(type, id)


		getFeedsOfFolderId: (id) ->
			@cache.getFeedsOfFolderId(id)


		getItemsByTypeAndId: (type, id) ->
			switch type
				when @feedType.Feed
					items = @cache.getItemsOfFeed(id) || []
					return items

				when @feedType.Subscriptions
					return @getItems()

				when @feedType.Folder
					items = []
					for feedId in @cache.getFeedIdsOfFolder(id)
						items = items.concat(@cache.getItemsOfFeed(feedId) || [])
					return items
				
				when @feedType.Starred
					return @cache.getImportantItems()


		setImportant: (itemId, isImportant) ->
			item = @getItemById(itemId)
			@cache.setImportant(item, isImportant)
			item.isImportant = isImportant


	return ItemModel

]