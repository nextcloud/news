###
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

angular.module('OC').factory '_Model', ->

	# Parent model: inherit your model from this object
	class Model

		constructor: ->
			@foreignKeys = {}
			@data = []
			@ids = {}


		handle: (data) ->
			if data['create'] != undefined
				for item in data['create']
					@create(item)

			if data['update'] != undefined
				for item in data['update']
					@update(item)

			if data['delete'] != undefined
				for item in data['delete']
					@delete(item)


		# @brief add a new foreign key name which caches data by foreign key
		# @param string name: the name of the foreign key property on the object
		# Foreign keys are caching items in a structure like
		# name -> id -> [item1, item2]
		hasForeignKey: (name) ->
			@foreignKeys[name] = {}


		# @brief adds a new object to the dataset
		# @param object data: the data that we want to store
		create: (data) ->
			if @ids[data.id] != undefined
				@update(data)
			else
				@data.push(data)
				@ids[data.id] = data

				# fill indizes of foreign keys
				for name, ids of @foreignKeys
					id = data[name]
					@foreignKeys[name][id] or= []
					@foreignKeys[name][id].push(data)


		# @brief updates an existing item, the id must not change
		# @param object item: the item which should be updated
		update: (item) ->
			currentItem = @ids[item.id]
			for key, value of item
				# if the foreignkey changed, we need to update the cache
				if @foreignKeys[key] != undefined
					if value != currentItem[key]
						@_updateForeignKeyCache(key, currentItem, item)
				if key != 'id'
					currentItem[key] = value


		delete: (item) ->
			if @getById(item.id) != undefined
				@removeById(item.id)


		_updateForeignKeyCache: (name, currentItem, toItem) ->
			fromValue = currentItem[name]
			toValue = toItem[name]
			foreignKeyItems = @foreignKeys[name][fromValue]
			@_removeForeignKeyCacheItem(foreignKeyItems, currentItem)
			@foreignKeys[name][toValue].push(item)


		_removeForeignKeyCacheItem: (foreignKeyItems, item) ->
			for fkItem, index in foreignKeyItems
				if fkItem.id == id
					@foreignKeys[key][item[key]].splice(index, 1)


		# @brief removes an object
		# @param int id: the id of the object
		removeById: (id) ->
			item = @getById(id)
			
			# remove from foreign key cache
			for key, ids of @foreignKeys
				foreignKeyItems = ids[item[key]]
				@_removeForeignKeyCacheItem(foreignKeyItems, item)

			# remove from array
			for item, index in @data
				if item.id == id
					@data.splice(index, 1)

			delete @ids[id]


		# @brief returns a data object by its id
		# @param int id: the id of the object that we want to fetch
		getById: (id) ->
			return @ids[id]


		# @brief returns all stored data objects
		getAll: ->
			return @data


		# @brief access the foreign key cache
		# @param string foreignKeyName: the name of the foreign key that we want to
		#                               look up
		# @param string foreignKeyId: the id from that foreign key that we want to
		#                             get
		getAllOfForeignKeyWithId: (foreignKeyName, foreignKeyId) ->
			return @foreignKeys[foreignKeyName][foreignKeyId]


	return Model