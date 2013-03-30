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


describe '_FolderBl', ->


	beforeEach module 'News'

	beforeEach inject (@_FolderBl, @FolderModel, @_FeedBl, @_ItemBl,
					@FeedModel, @ItemModel) =>
		@persistence =
			getItems: ->
		itemBl = new @_ItemBl(@ItemModel, @persistence)
		feedBl = new @_FeedBl(FeedModel, itemBl, @persistence)
		@bl = new @_FolderBl(@FolderModel, feedBl, @persistence)


	it 'should delete folders', =>
		@FolderModel.removeById = jasmine.createSpy('remove')
		@persistence.deleteFolder = jasmine.createSpy('deletequery')
		@bl.delete(3)

		expect(@FolderModel.removeById).toHaveBeenCalledWith(3)
		expect(@persistence.deleteFolder).toHaveBeenCalledWith(3)


	it 'should return true when folder has feeds', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		expect(@bl.hasFeeds(3)).toBeFalsy()

		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, urlHash: 'a2'})
		expect(@bl.hasFeeds(3)).toBeTruthy()


	it 'should toggle folder', =>
		@persistence.openFolder = jasmine.createSpy('open')
		@persistence.collapseFolder = jasmine.createSpy('collapse')

		@FolderModel.add({id: 3, open: false})
		@bl.toggleFolder(4)
		expect(@FolderModel.getById(3).open).toBeFalsy()

		@bl.toggleFolder(3)
		expect(@FolderModel.getById(3).open).toBeTruthy()
		expect(@persistence.openFolder).toHaveBeenCalledWith(3)

		@bl.toggleFolder(3)
		expect(@FolderModel.getById(3).open).toBeFalsy()
		expect(@persistence.collapseFolder).toHaveBeenCalledWith(3)


	it 'should mark folder as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, urlHash: 'a3'})

		@bl.markFolderRead(3)

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(2)