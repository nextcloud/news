###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt nukeawhale@gmail.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###

angular.module('News').directive 'itemShortcuts', ['$window', ($window) ->

	return (scope, elm, attr) ->

		jumpTo = ($scrollArea, $item) ->
			position = $item.offset().top - $scrollArea.offset().top +
				$scrollArea.scrollTop()
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


		getCurrentItem = (scrollArea) ->
			$scrollArea = $(scrollArea)
			$items = $scrollArea.find('.feed_item')
			for item in $items
				$item = $(item)
				# 130px of the item should be visible
				if ($item.height() + $item.position().top) > 110
					return $item


		keepUnreadCurrentItem = (scrollArea) ->
			$item = getCurrentItem(scrollArea)
			$item.find('.keep_unread').trigger('click')


		starCurrentItem = (scrollArea) ->
			$item = getCurrentItem(scrollArea)
			$item.find('.star').trigger('click')



		$($window.document).keydown (e) ->
			# only activate if no input elements is focused
			focused = $(':focus')

			if not (focused.is('input') or
			focused.is('select') or
			focused.is('textarea') or
			focused.is('checkbox') or
			focused.is('button'))

				scrollArea = elm
				# j or right or n
				if e.keyCode == 74 or e.keyCode == 39 or e.keyCode == 78
					jumpToNextItem(scrollArea)

				# k or left or p
				else if e.keyCode == 75 or e.keyCode == 37 or e.keyCode == 80
					jumpToPreviousItem(scrollArea)

				# u
				else if e.keyCode == 85
					keepUnreadCurrentItem(scrollArea)
					
				# s or i
				else if e.keyCode == 73 or e.keyCode == 83
					starCurrentItem(scrollArea)
					



]