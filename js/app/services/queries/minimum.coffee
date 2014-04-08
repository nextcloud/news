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


# A query for returning the minium of an array based on the object
angular.module('News').factory '_MinimumQuery', ['_Query',
(_Query) ->

	class MinimumQuery extends _Query

		constructor: (@_field) ->
			name = 'minimum'
			super(name, [@_field])


		exec: (data) ->
			minimum = undefined
			for entry in data
				if angular.isUndefined(minimum) or
				entry[@_field] < minimum[@_field]
					minimum = entry

			return minimum


	return MinimumQuery
]
