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

angular.module('News').directive 'onEnter', ->

	return (scope, elm, attr) ->

		elm.bind 'keyup', (e) ->
			if e.keyCode == 13
				e.preventDefault()
				scope.$apply attr.onEnter

