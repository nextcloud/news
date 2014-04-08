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

describe 'ocDraggable', ->

	beforeEach module 'News'

	beforeEach inject ($rootScope, $compile) =>
		@options =
			revert: true

		optionsString = JSON.stringify(@options).replace(/\"/g, '\'')
		@elm = angular.element('<div oc-draggable="' + optionsString + '"></div>')
		scope = $rootScope
		$compile(@elm)(scope)
		scope.$digest()


	it 'should bind jquery draggable', =>
		expect(@elm.is(':ui-draggable')).toBe(true)


	it 'should bind options if passed', =>
		expect(@elm.data('ui-draggable').options.revert).toBe(true)
