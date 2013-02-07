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

angular.module('News').factory '_Publisher', ->

	class Publisher

		constructor: () ->
			@subscriptions = {}


		subscribeTo: (type, object) ->
			@subscriptions[type] or= []
			@subscriptions[type].push(object)


		publish: (type, message) ->
			for subscriber in @subscriptions[type] || []
				subscriber.handle(message)


	return Publisher