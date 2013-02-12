###
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###

###
Used to forward clicks to another element via jquery selector

The expression which can be passed looks like this {selector:'#opml-upload'}
###

angular.module('OC').directive 'forwardClick', ->

	return (scope, elm, attr) ->
		options = scope.$eval(attr.forwardClick)

		if angular.isDefined(options.selector)
			elm.click ->
				$(options.selector).trigger('click')
