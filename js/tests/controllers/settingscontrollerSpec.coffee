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


describe 'SettingsController', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@persistence = {}
		$provide.value 'Persistence', @persistence
		return

	beforeEach inject ($controller, @ShowAll, @Compact) =>
		@scope = {}
		@replace =
			'$scope': @scope
			'FolderBusinessLayer':
				import: jasmine.createSpy('import')
			'FeedBusinessLayer':
				importArticles: jasmine.createSpy('import')
			'Compact': @Compact
		@controller = $controller('SettingsController', @replace)


	it 'should make FeedBl available', =>
		expect(@scope.feedBl).toBe(@FeedBl)


	it 'should show an error if the xml import failed', =>
		xml = 'test'
		@replace.FolderBusinessLayer.import.andCallFake ->
			throw new Error()

		@scope.import(xml)

		expect(@replace.FolderBusinessLayer.import).toHaveBeenCalledWith(xml)
		expect(@scope.error).toBe(true)


	it 'should set showall to true if importing', =>
		xml = 'test'

		@scope.import(xml)

		expect(@ShowAll.getShowAll()).toBe(true)


	it 'should set loading to true if importing json', =>
		json = "[\"test\"]"

		@scope.importArticles(json)
		expect(@scope.loading).toBe(true)


	it 'should show an error if the json import failed', =>
		json = 'test'

		@scope.importArticles(json)

		expect(@scope.jsonError).toBe(true)


	it 'should import json', =>
		json = "{\"test\": \"abc\"}"

		@scope.importArticles(json)

		expected = JSON.parse(json)
		expect(@replace.FeedBusinessLayer.importArticles).toHaveBeenCalledWith(
			expected, jasmine.any(Function)
		)


	it 'should set the compact view', =>
		@persistence.userSettingsSetCompact = jasmine.createSpy('compact')

		@Compact.handle(false)
		@scope.setCompactView()

		expect(@persistence.userSettingsSetCompact).toHaveBeenCalledWith(true)
		expect(@scope.isCompactView()).toBe(true)