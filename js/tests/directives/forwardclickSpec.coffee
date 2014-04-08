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

describe 'ocForwardClick', ->

	beforeEach module 'News'


	beforeEach inject ($rootScope, $compile) =>
		@$rootScope = $rootScope
		@$compile = $compile
		@host = $('<div id="host"></div>')
		$('body').append(@host)


	@setOptions = (options) =>
		if angular.isDefined(options.selector)
			json = JSON.stringify(options)
			optionsString = json.replace(/\"/g, '\'')
		else
			optionsString = ""

		elm = '<div>' +
				'<div id="a" oc-forward-click="' + optionsString + '"></div>' +
				'<input onclick="this.value=\'clicked\'" value="not-clicked" ' +
					'type="text" id="b" />' +
			'</div>'

		@elm = angular.element(elm)
		scope = @$rootScope
		@$compile(@elm)(scope)
		scope.$digest()
		@host.append(@elm)


	it 'should not forward clicks if no selector is given', =>
		options = {}
		@setOptions(options)
		@elm.find('#a').trigger('click')
		expect(@elm.find('#b').val()).toBe('not-clicked')


	it 'should forward click to item if selector is given', =>
		options =
			selector: '#b'
		@setOptions(options)
		@elm.find('#a').trigger('click')
		expect(@elm.find('#b').val()).toBe('clicked')


	afterEach =>
		@host.remove()