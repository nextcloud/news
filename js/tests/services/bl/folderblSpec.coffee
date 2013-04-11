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


describe 'FolderBl', ->

	beforeEach module 'News'

	beforeEach =>
		angular.module('News').factory 'Persistence', =>
			@persistence =
				createFolder: ->

	beforeEach inject (@FolderBl, @FolderModel,	@FeedModel, @ShowAll,
		               @ActiveFeed, @FeedType, @_ExistsError) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Feed, id:0})



	it 'should delete folders', =>
		@FolderModel.removeById = jasmine.createSpy('remove')
		@persistence.deleteFolder = jasmine.createSpy('deletequery')
		@FolderBl.delete(3)

		expect(@FolderModel.removeById).toHaveBeenCalledWith(3)
		expect(@persistence.deleteFolder).toHaveBeenCalledWith(3)


	it 'should return true when folder has feeds', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		expect(@FolderBl.hasFeeds(3)).toBeFalsy()

		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, urlHash: 'a2'})
		expect(@FolderBl.hasFeeds(3)).toBeTruthy()


	it 'should toggle folder', =>
		@persistence.openFolder = jasmine.createSpy('open')
		@persistence.collapseFolder = jasmine.createSpy('collapse')

		@FolderModel.add({id: 3, opened: false, name: 'ho'})
		@FolderBl.toggleFolder(4)
		expect(@FolderModel.getById(3).opened).toBeFalsy()

		@FolderBl.toggleFolder(3)
		expect(@FolderModel.getById(3).opened).toBeTruthy()
		expect(@persistence.openFolder).toHaveBeenCalledWith(3)

		@FolderBl.toggleFolder(3)
		expect(@FolderModel.getById(3).opened).toBeFalsy()
		expect(@persistence.collapseFolder).toHaveBeenCalledWith(3)


	it 'should mark folder as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, urlHash: 'a3'})

		@FolderBl.markFolderRead(3)

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(2)


	it 'should get the correct unread count', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		@FeedModel.add({id: 6, unreadCount:3, folderId: 3, urlHash: 'a2'})
		@FeedModel.add({id: 7, unreadCount:4, folderId: 2, urlHash: 'a3'})

		expect(@FolderBl.getUnreadCount(2)).toBe(6)


	it 'should be visible if show all is true', =>
		expect(@FolderBl.isVisible(3)).toBe(false)

		@ShowAll.setShowAll(true)
		expect(@FolderBl.isVisible(3)).toBe(true)


	it 'should be visible if its active', =>
		@ActiveFeed.handle({type: @FeedType.Folder, id:3})
		expect(@FolderBl.isVisible(3)).toBe(true)


	it 'should be visible if one of its subfeeds is active', =>
		@FeedModel.add({id: 5, unreadCount:0, folderId: 2, urlHash: 'a1'})
		@FeedModel.add({id: 6, unreadCount:0, folderId: 3, urlHash: 'a2'})
		@FeedModel.add({id: 7, unreadCount:0, folderId: 2, urlHash: 'a3'})

		@ActiveFeed.handle({type: @FeedType.Feed, id:6})
		expect(@FolderBl.isVisible(3)).toBe(true)


	it 'should be visible if showAll is false and it has unread items', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		@FeedModel.add({id: 6, unreadCount:3, folderId: 3, urlHash: 'a2'})
		@FeedModel.add({id: 7, unreadCount:4, folderId: 2, urlHash: 'a3'})

		@ActiveFeed.handle({type: @FeedType.Folder, id:2})
		expect(@FolderBl.isVisible(3)).toBe(true)


	it 'should return all folders', =>
		item1 = {id: 3, open: false, name: 'ho'}
		item2 = {id: 4, open: true, name: 'hod'}
		@FolderModel.add(item1)
		@FolderModel.add(item2)

		expect(@FolderBl.getAll()).toContain(item1)
		expect(@FolderBl.getAll()).toContain(item2)


	it 'should not create a folder if it already exists', =>
		item1 = {id: 4, open: true, name: 'john'}
		@FolderModel.add(item1)

		expect =>
			@FolderBl.create('john')
		.toThrow(new @_ExistsError())
		
		expect =>
			@FolderBl.create('johns')
		.not.toThrow(new @_ExistsError())


	it 'should not create folders that are empty', =>
		expect =>
			@FolderBl.create('   ')
		.toThrow(new Error())


	it 'should create a folder before theres a response from the server', =>
		@FolderBl.create('johns')
		expect(@FolderModel.size()).toBe(1)


	it 'should make a create folder request', =>
		@persistence.createFolder = jasmine.createSpy('add folder')
		
		@FolderBl.create(' johns ')
		expect(@persistence.createFolder).toHaveBeenCalledWith('johns', 0,
			jasmine.any(Function))


	it 'should call the onSuccess function on response status ok', =>
		onSuccess = jasmine.createSpy('Success')
		@persistence.createFolder = jasmine.createSpy('add folder')
		@persistence.createFolder.andCallFake (folderName, parentId, success) =>
			response =
				status: 'ok'
			success(response)

		@FolderBl.create(' johns ', onSuccess)

		expect(onSuccess).toHaveBeenCalled()


	it 'should call the handle a response error when creating a folder', =>
		onSuccess = jasmine.createSpy('Success')
		onFailure = jasmine.createSpy('Failure')
		@persistence.createFolder = jasmine.createSpy('add folder')
		@persistence.createFolder.andCallFake (folderName, parentId, success) =>
			@response =
				status: 'error'
				msg: 'this is an error'
			success(@response)

		@FolderBl.create(' johns ', onSuccess, onFailure)

		expect(onSuccess).not.toHaveBeenCalled()
		expect(onFailure).toHaveBeenCalled()

		expect(@FolderModel.getByName('johns').error).toBe(@response.msg)


	it 'should mark a folder error as read by removing it', =>
		@FolderModel.add({id: 3, name: 'john'})

		@FolderBl.markErrorRead('John')

		expect(@FolderModel.size()).toBe(0)
		expect(@FolderModel.getByName('john')).toBe(undefined)

