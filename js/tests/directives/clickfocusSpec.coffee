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

describe 'ocClickFocus', ->

	beforeEach module 'News'


	beforeEach inject ($rootScope, $compile, @$timeout) =>
		@$rootScope = $rootScope
		@$compile = $compile
		@host = $('<div id="host"></div>')
		$('body').append(@host)
		$.fx.off = true


	it 'should focus element', =>
		elm = '<a 	href="#" ' +
					'oc-click-focus="{selector: \'#shouldfocus\'}" ' +
					'id="clicker"' +
					'onclick="this.href=\'hi\'">test</a>' +
			'<div><input id="shouldfocus" type="text" /></div>'
		@elm = angular.element(elm)
		scope = @$rootScope
		@$compile(@elm)(scope)
		scope.$digest()
		@host.append(@elm)

		$(@host).find('#clicker').trigger 'click'
		focused = document.activeElement == $(@host).find('#shouldfocus').get(0)
		expect(focused).toBe(true)


	it 'should execute the function when a timeout is being used', =>
		elm = '<a 	href="#" ' +
					'oc-click-focus="{selector: \'#shouldfocus\', ' +
					'timeout: 3000}" ' +
					'id="clicker"' +
					'onclick="this.href=\'hi\'">test</a>' +
			'<div><input id="shouldfocus" type="text" /></div>'
		@elm = angular.element(elm)
		scope = @$rootScope
		@$compile(@elm)(scope)
		scope.$digest()
		@host.append(@elm)

		$(@host).find('#clicker').trigger 'click'
		@$timeout.flush()
		focused = document.activeElement == $(@host).find('#shouldfocus').get(0)
		expect(focused).toBe(true)


	afterEach =>
		@host.remove()