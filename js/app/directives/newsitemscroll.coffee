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

scrolling = true
markingRead = true

angular.module('News').directive 'newsItemScroll', ['$rootScope', 'Config',
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
						$elems = elm.find('.feed_item:not(.read)')

						for feedItem in $elems
							offset = $(feedItem).position().top
							if offset <= -50
								id = parseInt($(feedItem).data('id'), 10)
								$rootScope.$broadcast 'readItem', id

							else
								break

					, Config.MarkReadTimeout

				# autopaging
				counter = 0

				# run from the bottom up to be performant
				for item in elm.find('.feed_item') by -1

					# if the counter is 10 it means that it didnt break to auto
					# page yet and that there are more than 10 items, so break
					if counter >= Config.autoPageFactor
						break

					# this is only reached when the item is not is below the top
					# and we didnt hit the factor yet so autopage and break
					if $(item).position().top < 0
						$rootScope.$broadcast 'autoPage'
						break

					counter += 1

]

