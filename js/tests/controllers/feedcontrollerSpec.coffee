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


describe 'FeedController', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@imagePath = jasmine.createSpy('imagePath')
		@utils =
			imagePath: @imagePath
		$provide.value 'Utils', @utils

		@persistence = {}

		$provide.value 'Persistence', @persistence
		return

	beforeEach inject ($controller, @FolderBusinessLayer, @FeedBusinessLayer,
	                   $rootScope, @unreadCountFormatter, @FeedModel,
	                   @SubscriptionsBusinessLayer, @StarredBusinessLayer,
	                   @$window, @_ExistsError, @FolderModel, @FeedType) =>
		@scope = $rootScope.$new()
		replace =
			$scope: @scope

		@$window.document.title = ''

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


	it 'should set the window title to the total unread count', =>
		@scope.translations =
			appName: 'News'
		expect(@$window.document.title).toBe('')

		@scope.getTotalUnreadCount()
		expect(@$window.document.title).toBe('News | ownCloud')

		item = {id: 3, unreadCount: 5, faviconLink: 'test', url: 'hi'}
		@FeedModel.add(item)
		@scope.getTotalUnreadCount()

		expect(@$window.document.title).toBe('News (5) | ownCloud')


	it 'should show 99+ if in window title when more than 99 unread count', =>
		@scope.translations =
			appName: 'News'
		item = {id: 3, unreadCount: 1, faviconLink: 'test', url: 'hi'}
		item1 = {id: 5, unreadCount: 999, faviconLink: 'test', url: 'his'}
		@FeedModel.add(item)
		@FeedModel.add(item1)

		@scope.getTotalUnreadCount()

		expect(@$window.document.title).toBe('News (999+) | ownCloud')


	it 'should move a feed if moveFeedToFolder is broadcasted', =>
		item = {id: 3, unreadCount: 1, faviconLink: 'test', url: 'hi'}
		@FeedModel.add(item)
		@persistence.moveFeed = jasmine.createSpy('move feed')
		@scope.$broadcast 'moveFeedToFolder', {feedId: 3, folderId: 1}

		expect(@persistence.moveFeed).toHaveBeenCalledWith(3, 1)


	it 'should set isAddingFolder to true if there were no problems', =>
		@persistence.createFolder = jasmine.createSpy('create')
		@scope.addFolder(' Ola')
		expect(@scope.isAddingFolder()).toBe(true)


	it 'should set isAddingFolder to false after a failed request', =>
		@persistence.createFolder = jasmine.createSpy('create')
		@persistence.createFolder.andCallFake (name, id, onSuccess, onFailure) ->
			onFailure()

		@scope.addFolder(' Ola')
		expect(@scope.isAddingFolder()).toBe(false)


	it 'should show an error if the folder exists and reset the input', =>
		@FolderBusinessLayer.create = jasmine.createSpy('create')
		@FolderBusinessLayer.create.andCallFake =>
			throw new @_ExistsError('ye')

		@scope.addFolder(' Ola')

		expect(@scope.folderExistsError).toBe(true)
		expect(@scope.isAddingFolder()).toBe(false)


	it 'should reset the add folder form and set the created as selected', =>
		@persistence.createFolder = jasmine.createSpy('create')
		data =
			folders: [
				{id: 3, name: 'soba'}
			]
		@persistence.createFolder.andCallFake (id, parent, onSuccess) =>
			@FolderModel.handle(data.folders)
			onSuccess(data)

		@scope.addFolder(' Soba')

		expect(@scope.folderName).toBe('')
		expect(@scope.addNewFolder).toBe(false)
		expect(@scope.isAddingFolder()).toBe(false)
		expect(@scope.folderId.name).toBe('soba')


	it 'should set isAddingFeed to true if there were no problems', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@scope.addFeed('Ola')
		expect(@scope.isAddingFeed()).toBe(true)


	it 'should set isAddingFeed to false after a failed request', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@persistence.createFeed.andCallFake (name, id, onSuccess, onFailure) ->
			onFailure()

		@scope.addFolder(' Ola')
		expect(@scope.isAddingFeed()).toBe(false)


	it 'should show an error if the feed exists and reset the input', =>
		@FeedBusinessLayer.create = jasmine.createSpy('create')
		@FeedBusinessLayer.create.andCallFake =>
			throw new @_ExistsError('ye')

		@scope.addFeed(' Ola')

		expect(@scope.feedExistsError).toBe(true)
		expect(@scope.isAddingFeed()).toBe(false)


	it 'should open the parent folder of the added feed', =>
		item = {opened: false, id: 3, name: 'john'}
		@FolderModel.add(item)

		@scope.addFeed(' Ola', 3)

		expect(item.opened).toBe(true)


	it 'should reset the add feed form and load the added feed', =>
		@persistence.createFeed = jasmine.createSpy('create')
		@persistence.getItems = jasmine.createSpy('load')

		data =
			feeds: [
				{id: 3, url: 'http://soba', title: 'hi'}
			]
			status: 'success'
		@persistence.createFeed.andCallFake (id, parent, onSuccess) =>
			@FeedModel.handle(data.feeds)
			onSuccess(data)

		@scope.addFeed(' Soba')

		expect(@scope.feedUrl).toBe('')
		expect(@scope.isAddingFeed()).toBe(false)
		expect(@persistence.getItems).toHaveBeenCalledWith(
			@FeedType.Feed, 3, 0, jasmine.any(Function)
		)