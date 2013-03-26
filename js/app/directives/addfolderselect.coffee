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


###
Turns a normal select into a folder select with the ability to create new
folders
###
angular.module('News').directive 'addFolderSelect', ['$rootScope', ->

	return (scope, elm, attr) ->

		options =
			singleSelect: true
			selectedFirst: true
			createText: $(elm).data('create')
			createdCallback: (selected, value) ->
				console.log selected
				console.log value

		$(elm).multiSelect(options)

]