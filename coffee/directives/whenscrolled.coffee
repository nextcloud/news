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

scrolling = true
markingRead = true

angular.module('News').directive 'whenScrolled',
['$rootScope', 'Config',
($rootScope, Config) ->

	return (scope, elm, attr) ->

		elm.bind 'scroll', ->
			# prevent from doing to many scroll actions
			# the first timeout prevents accidental and too early marking as read
			if scrolling
				scrolling = false
				setTimeout ->
					scrolling = true
				, Config.ScrollTimeout

				if markingRead
					markingRead = false
					setTimeout ->
						markingRead = true
						# only broadcast elements that are not already read
						# and that are beyond the top border
						$elems = $(elm).find('.feed_item:not(.read)')

						for feedItem in $elems
							offset = $(feedItem).position().top
							if offset <= -50
								id = parseInt($(feedItem).data('id'), 10)
								feed = parseInt($(feedItem).data('feed'), 10)
								$rootScope.$broadcast('read', {id: id, feed: feed})
							else
								break

					, Config.MarkReadTimeout

				scope.$apply attr.whenScrolled

]

