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


# Parentclass to inherit from for defining own model query
angular.module('News').factory '_Query', ['_NotImplementedError',
(_NotImplementedError) ->

	class Query

		constructor: (@_name, @_args=[]) ->


		exec: (data) ->
			throw new _NotImplementedError('Not implemented')


		hashCode: (filter) ->
			hash = @_name
			for arg in @_args
				if angular.isString(arg)
					arg = arg.replace(/_/gi, '__')
				hash += '_' + arg

			return hash


	return Query

]