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

angular.module('News').directive 'feedNavigation', ->

	return (scope, elm, attr) ->

		jumpTo = ($scrollArea, $item) ->
			position = $item.offset().top - $scrollArea.offset().top + $scrollArea.scrollTop()
			$scrollArea.scrollTop(position)

		jumpToPreviousItem = (scrollArea) ->
			$scrollArea = $(scrollArea)
			$items = $scrollArea.find('.feed_item')
			notJumped = true
			for item in $items
				$item = $(item)
				if $item.position().top >= 0
					$previous = $item.prev()
					# if there are no items before the current one
					if $previous.length > 0
						jumpTo($scrollArea, $previous)

					notJumped = false
					break

			# in case we didnt jump
			if $items.length > 0 and notJumped
				jumpTo($scrollArea, $items.last())


		jumpToNextItem = (scrollArea) ->
			$scrollArea = $(scrollArea)
			$items = $scrollArea.find('.feed_item')
			for item in $items
				$item = $(item)
				if $item.position().top > 1
					jumpTo($scrollArea, $item)
					break


		$(document).keydown (e) ->
			# only activate if no input elements is focused
			focused = $(':focus')

			if not (focused.is('input') or focused.is('select') or 
			focused.is('textarea') or focused.is('checkbox') or focused.is('button'))

				scrollArea = elm
				# j or right
				if e.keyCode == 74 or e.keyCode == 39
					jumpToNextItem(scrollArea)

				# k or left
				else if e.keyCode == 75 or e.keyCode == 37
					jumpToPreviousItem(scrollArea)


