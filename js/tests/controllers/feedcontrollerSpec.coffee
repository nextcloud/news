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


describe '_FeedController', ->


	beforeEach module 'News'


	beforeEach inject (@_FeedController, @ActiveFeed, @ShowAll, @FeedType,
		               @StarredCount, @FeedModel, @FolderModel, @FeedBl) =>
		@scope =
			$on: ->

		@persistence =
			getItems: ->
				
		@controller = new @_FeedController(@scope, @FolderModel, @FeedModel,
			                               @ActiveFeed, @ShowAll, @FeedType,
			                               @StarredCount, @persistence,
			                               @FeedBl)


	xit 'should make folders available', =>
		@FolderModel.getAll = jasmine.createSpy('FolderModel')
		new @_FeedController(@scope, @FolderModel, @FeedModel, @_ActiveFeed)

		expect(@FolderModel.getAll).toHaveBeenCalled()


	xit 'should make feeds availabe', =>
		@FeedModel.getAll = jasmine.createSpy('FeedModel')
		new @_FeedController(@scope, @FolderModel, @FeedModel, @_ActiveFeed)

		expect(@FeedModel.getAll).toHaveBeenCalled()


	xit 'should make feedtype available', =>
		expect(@scope.feedType).toBe(@FeedType)


	xit 'should check the active feed', =>
		@ActiveFeed.getType = =>
			return @FeedType.Feed
		@ActiveFeed.getId = =>
			return 5

		expect(@scope.isFeedActive(@FeedType.Feed, 5)).toBeTruthy()


	xit 'should provide ShowAll', =>
		expect(@scope.isShowAll()).toBeFalsy()
		
		@ShowAll.setShowAll(true)
		expect(@scope.isShowAll()).toBeTruthy()


	xit 'should handle show all correctly', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('hide')
		@persistence.userSettingsReadShow = jasmine.createSpy('show')

		@scope.setShowAll(true)
		expect(@ShowAll.getShowAll()).toBeTruthy()
		expect(@persistence.userSettingsReadShow).toHaveBeenCalled()
		expect(@persistence.userSettingsReadHide).not.toHaveBeenCalled()


	xit 'should handle hide all correctly', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('hide')
		@persistence.userSettingsReadShow = jasmine.createSpy('show')

		@scope.setShowAll(false)
		expect(@ShowAll.getShowAll()).toBeFalsy()
		expect(@persistence.userSettingsReadShow).not.toHaveBeenCalled()
		expect(@persistence.userSettingsReadHide).toHaveBeenCalled()


	xit 'should get the correct count for starred items', =>
		@StarredCount.setStarredCount(133)
		count = @scope.getUnreadCount(@FeedType.Starred, 0)

		expect(count).toBe(133)


	xit 'should set the count to 999+ if the count is over 999', =>
		@StarredCount.setStarredCount(1000)
		count = @scope.getUnreadCount(@FeedType.Starred, 0)

		expect(count).toBe('999+')





	xit 'should set active feed to new feed if changed', =>
		@ActiveFeed.handle({id: 3, type: 3})
		@scope.loadFeed(4, 3)

		expect(@ActiveFeed.getId()).toBe(3)
		expect(@ActiveFeed.getType()).toBe(4)


	xit 'should return true when calling isShown and there are feeds', =>
		@FeedModel.add({id: 3})
		@ShowAll.setShowAll(true)
		expect(@scope.isShown(3, 4)).toBeTruthy()

		@ShowAll.setShowAll(false)
		expect(@scope.isShown(3, 4)).toBeFalsy()


	xit 'should return true if ShowAll is false but unreadcount is not 0', =>
		@ShowAll.setShowAll(false)
		@FeedModel.add({id: 4, unreadCount: 0, urlHash: 'a1'})
		expect(@scope.isShown(@FeedType.Feed, 4)).toBeFalsy()

		@FeedModel.add({id: 4, unreadCount: 12, urlHash: 'a2'})
		expect(@scope.isShown(@FeedType.Feed, 4)).toBeTruthy()


	xit 'isAddingFolder should return false in the beginning', =>
		expect(@scope.isAddingFolder()).toBeFalsy()


	xit 'isAddingFeed should return false in the beginning', =>
		expect(@scope.isAddingFeed()).toBeFalsy()


	xit 'should not add folders that have no name', =>
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

