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

###
This directive can be bound on an input element with type file
When a file is input, the content will be passed to the given function as
$fileContent parameter
###
angular.module('News').directive 'ocReadFile',
['$rootScope', ($rootScope) ->

	return (scope, elm, attr) ->
		elm.change ->

			file = elm[0].files[0]
			reader = new FileReader()

			reader.onload = (e) ->
				elm[0].value = null
				scope.$fileContent = e.target.result
				scope.$apply attr.ocReadFile
			
			reader.readAsText(file)

]