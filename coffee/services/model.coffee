###
# ownCloud - News app
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

angular.module('News').factory 'Model', ->

	class Model

		constructor: () ->
			@clearCache()

		handle: (data) ->
			for item in data
				@add(item)


		clearCache: () ->
			@items = []
			@itemIds = {}
			@markAccessed()


		markAccessed: () ->
			@lastAccessed = new Date().getTime()


		getLastModified: () ->
			return @lastAccessed


		add: (item) ->
			if @itemIds[item.id] == undefined
				@items.push(item)
				@itemIds[item.id] = item
				@markAccessed()
				return true
			else
				@update(item)
				return false


		update: (item) ->
			updatedItem = @itemIds[item.id]
			for key, value of item
				if key != 'id'
					updatedItem[key] = value
			@markAccessed()


		removeById: (id) ->
			removeItemIndex = null
			counter = 0
			for item in @items
				if item.id == id
					removeItemIndex = counter
					break
				counter += 1

			if removeItemIndex != null
				@items.splice(removeItemIndex, 1)
				delete @itemIds[id]
			@markAccessed()


		getItemById: (id) ->
			return @itemIds[id]


		getItems: () ->
			return @items


	return Model