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


describe 'FeedController', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@persistence = {}
		$provide.value 'Persistence', @persistence
		return

	beforeEach inject ($controller, @FolderBusinessLayer, @FeedBusinessLayer,
	                   $rootScope, @unreadCountFormatter,
	                   @SubscriptionsBusinessLayer, @StarredBusinessLayer) =>
		@scope = $rootScope.$new()
		replace =
			$scope: @scope

		@controller = $controller('FeedController', replace)


	it 'isAddingFolder should return false in the beginning', =>
		expect(@scope.isAddingFolder()).toBeFalsy()


	it 'isAddingFeed should return false in the beginning', =>
		expect(@scope.isAddingFeed()).toBeFalsy()


	it 'should make unreadCountFormatter available', =>
		expect(@scope.unreadCountFormatter).toBe(@unreadCountFormatter)


	it 'should make FeedBusinessLayer available', =>
		expect(@scope.feedBusinessLayer).toBe(@FeedBusinessLayer)


	it 'should make FolderBusinessLayer available', =>
		expect(@scope.folderBusinessLayer).toBe(@FolderBusinessLayer)


	it 'should make SubscriptionsBusinessLayer available', =>
		expect(@scope.subscriptionsBusinessLayer).toBe(
			@SubscriptionsBusinessLayer)

	it 'should make StarredBusinessLayer available', =>
		expect(@scope.starredBusinessLayer).toBe(@StarredBusinessLayer)

	it 'should not add folders that have no name', =>
		@persistence.createFolder = jasmine.createSpy('create')
		@scope.addFolder(' ')

		expect(@scope.folderEmptyError).toBeTruthy()
		expect(@persistence.createFolder).not.toHaveBeenCalled()


	xit 'should not add folders that already exist client side', =>
		@FolderModel.add({id: 3, name: 'ola'})
		@persistence.createFolder = jasmine.createSpy('create')
		@scope.addFolder(' Ola')

		expect(@scope.folderExistsError).toBeTruthy()
		expect(@persistence.createFolder).not.toHaveBeenCalled()


	xit 'should set isAddingFolder to true if there were no problems', =>
		@persistence.createFolder = jasmine.createSpy('create')
		@scope.addFolder(' Ola')
		expect(@scope.isAddingFolder()).toBeTruthy()


	xit 'should create a create new folder request if everything was ok', =>
		@persistence.createFolder = jasmine.createSpy('create')
		@scope.addFolder(' Ola')
		expect(@persistence.createFolder).toHaveBeenCalled()
		expect(@persistence.createFolder.argsForCall[0][0]).toBe('Ola')
		expect(@persistence.createFolder.argsForCall[0][1]).toBe(0)


	xit 'should should reset the foldername on and set isAddingFolder to false',=>
		@persistence.createFolder =
			jasmine.createSpy('create').andCallFake (arg1, arg2, func) =>
				func()
		@scope.addFolder(' Ola')

		expect(@scope.folderName).toBe('')
		expect(@scope.isAddingFolder()).toBeFalsy()
		expect(@scope.addNewFolder).toBeFalsy()


	xit 'should not add feeds that have no url', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@scope.addFeed(' ')

		expect(@scope.feedEmptyError).toBeTruthy()
		expect(@persistence.createFeed).not.toHaveBeenCalled()


	xit 'should set isAddingFeed to true if there were no problems', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@scope.addFeed('ola')
		expect(@scope.isAddingFeed()).toBeTruthy()


	xit 'should should reset the feedurl and set isAddingFeed to false on succ',=>
		@persistence.createFeed =
			jasmine.createSpy('create').andCallFake (arg1, arg2, func) =>
				data =
					status: 'success'
				func(data)
		@scope.addFeed(' Ola')

		expect(@scope.feedUrl).toBe('')
		expect(@scope.isAddingFeed()).toBeFalsy()


	xit 'should should set isAddingFeed to false on err',=>
		@persistence.createFeed =
			jasmine.createSpy('create').andCallFake (arg1, arg2, func, err) =>
				err()
		@scope.addFeed('Ola')

		expect(@scope.isAddingFeed()).toBeFalsy()
		expect(@scope.feedError).toBeTruthy()


	xit 'should should set isAddingFeed to false on serverside error',=>
		@persistence.createFeed =
			jasmine.createSpy('create').andCallFake (arg1, arg2, func) =>
				data =
					status: 'error'
				func(data)
		@scope.addFeed('Ola')

		expect(@scope.isAddingFeed()).toBeFalsy()
		expect(@scope.feedError).toBeTruthy()


	xit 'should create a create new feed request if everything was ok', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@scope.addFeed('Ola')
		expect(@persistence.createFeed).toHaveBeenCalled()
		expect(@persistence.createFeed.argsForCall[0][0]).toBe('Ola')
		expect(@persistence.createFeed.argsForCall[0][1]).toBe(0)

