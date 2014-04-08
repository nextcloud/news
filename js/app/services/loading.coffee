###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt dev@bernhard-posselt.com

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


# Simple class which can be used to show loading events when ajax events are
# fired
angular.module('News').factory '_Loading', ->

	class Loading

		constructor: ->
			@_count = 0


		increase: ->
			@_count += 1


		decrease: ->
			@_count -= 1


		getCount: ->
			return @_count


		isLoading: ->
			return @_count > 0


	return Loading