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

describe 'ocClickSlideToggle', ->

	beforeEach module 'News'


	beforeEach inject ($rootScope, $compile) =>
		@$rootScope = $rootScope
		@$compile = $compile
		@host = $('<div id="host"></div>')
		$('body').append(@host)
		$.fx.off = true


	@setOptions = (options) =>
		if angular.isDefined(options.selector)
			json = JSON.stringify(options)
			optionsString = json.replace(/\"/g, '\'')
		else
			optionsString = ""

		elm = '<div>' +
				'<div style="display: none;" id="a" ' +
				'oc-click-slide-toggle="' + optionsString + '"></div>' +
				'<div style="display: none;" id="b"></div>' +
				'<div style="display: none;" id="c"></div>' +
			'</div>'

		@elm = angular.element(elm)
		scope = @$rootScope
		@$compile(@elm)(scope)
		scope.$digest()
		@host.append(@elm)


	it 'should not show div hidden divs', =>
		@setOptions({})
		expect(@elm.find('#a').is(':visible')).toBe(false)
		expect(@elm.find('#b').is(':visible')).toBe(false)
		expect(@elm.find('#c').is(':visible')).toBe(false)


	it 'should slide up div on click', =>
		@setOptions({})
		a = @elm.find('#a')
		a.trigger 'click'

		expect(a.is(':visible')).toBe(true)



	xit 'should slide up other element if selector is passed', =>
		# FIXME: run async
		options =
			selector: '#b'

		@setOptions(options)

		a = @elm.find('#a')
		b = @elm.find('#b')

		a.trigger 'click'
		expect(b.is(':visible')).toBe(true)


	xit 'should hide div when other div was clicked', =>
		# FIXME: run async
		options =
			selector: '#b'
			callback: =>
				@elm.find('#c').trigger 'click'
				expect(@elm.find('#a').is(':animated')).toBe(true)

		@setOptions(options)
		@elm.find('#a').trigger 'click'


	xit 'should not hide current slid up element on click but others', =>
		# FIXME: run async
		called = 0
		callback = =>
			if called == 2
				@elm.find('#c').trigger 'click'
				expect(@elm.find('#b').is(':animated')).toBe(true)
				expect(@elm.find('#c').is(':animated')).toBe(false)
			else
				called += 1

		options =
			selector: '#b'
			callback: ->
				callback()
		@setOptions(options)

		options =
			selector: '#c'
			callback: ->
				callback()
		@setOptions(options)

		@elm.find('#a').trigger 'click'




	afterEach =>
		@host.remove()