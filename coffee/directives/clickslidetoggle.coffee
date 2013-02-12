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

###
Used to slide up an area and can be customized by passing an expression.
If selector is defined, a different area is slid up on click
If hideOnFocusLost is defined, the slid up area will hide when the focus is lost
###
angular.module('News').directive 'clickSlideToggle',
['$rootScope', ($rootScope) ->

	return (scope, elm, attr) ->
		options = scope.$eval(attr.clickSlideToggle)

		if angular.isDefined(options.selector)
			slideArea = $(options.selector)
		else
			slideArea = elm

		elm.click ->
			if slideArea.is(':visible') and not slideArea.is(':animated')
				slideArea.slideUp()
			else
				slideArea.slideDown()

		if angular.isDefined(options.hideOnFocusLost) and options.hideOnFocusLost
			$(document.body).click ->
                                $rootScope.$broadcast 'lostFocus'

                        $rootScope.$on 'lostFocus', (scope, params) ->
                                if params != slideArea
                                        if slideArea.is(':visible') and not slideArea.is(':animated')
                                                slideArea.slideUp()

			slideArea.click (e) ->
                                $rootScope.$broadcast 'lostFocus', slideArea
				e.stopPropagation()

			elm.click (e) ->
                                $rootScope.$broadcast 'lostFocus', slideArea
				e.stopPropagation()

]