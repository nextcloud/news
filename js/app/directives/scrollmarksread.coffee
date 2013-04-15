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

angular.module('News').directive 'scrollMarksRead', ['$rootScope', 'Config',
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
								data =
									id: parseInt($(feedItem).data('id'), 10)
									feed: parseInt($(feedItem).data('feed'), 10)

								$rootScope.$broadcast 'readItem', data
							else
								break

					, Config.MarkReadTimeout

				scope.$apply attr.scrollMarksRead

]

