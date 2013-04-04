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
			@persistence = {}

	beforeEach inject (@FolderBl, @FolderModel,	@FeedModel, @ShowAll,
		               @ActiveFeed, @FeedType) =>
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

		@FolderModel.add({id: 3, open: false})
		@FolderBl.toggleFolder(4)
		expect(@FolderModel.getById(3).open).toBeFalsy()

		@FolderBl.toggleFolder(3)
		expect(@FolderModel.getById(3).open).toBeTruthy()
		expect(@persistence.openFolder).toHaveBeenCalledWith(3)

		@FolderBl.toggleFolder(3)
		expect(@FolderModel.getById(3).open).toBeFalsy()
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
		item1 = {id: 3, open: false}
		item2 = {id: 4, open: true}
		@FolderModel.add(item1)
		@FolderModel.add(item2)

		expect(@FolderBl.getAll()).toContain(item1)
		expect(@FolderBl.getAll()).toContain(item2)


