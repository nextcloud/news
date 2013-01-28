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

angular.module('News').factory '_ActiveFeed', ->

	class ActiveFeed

		constructor: ->
			@id = 0
			@type = 3

		handle: (data) ->
			@id = data.id
			@type = data.type

	return ActiveFeed
