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
		               @StarredCount, @FeedModel, @FolderModel, @ItemModel) =>
		@scope = {}
		@persistence = {
			getItems: ->
		}
		@controller = new @_FeedController(@scope, @FolderModel, @FeedModel,
			                               @ActiveFeed, @ShowAll, @FeedType,
			                               @StarredCount, @persistence,
			                               @ItemModel)


	it 'should make folders available', =>
		@FolderModel.getAll = jasmine.createSpy('FolderModel')
		new @_FeedController(@scope, @FolderModel, @FeedModel, @_ActiveFeed)

		expect(@FolderModel.getAll).toHaveBeenCalled()


	it 'should make feeds availabe', =>
		@FeedModel.getAll = jasmine.createSpy('FeedModel')
		new @_FeedController(@scope, @FolderModel, @FeedModel, @_ActiveFeed)

		expect(@FeedModel.getAll).toHaveBeenCalled()


	it 'should make feedtype available', =>
		expect(@scope.feedType).toBe(@FeedType)


	it 'should check the active feed', =>
		@ActiveFeed.getType = =>
			return @FeedType.Feed
		@ActiveFeed.getId = =>
			return 5

		expect(@scope.isFeedActive(@FeedType.Feed, 5)).toBeTruthy()


	it 'should provide ShowAll', =>
		expect(@scope.isShowAll()).toBeFalsy()
		
		@ShowAll.setShowAll(true)
		expect(@scope.isShowAll()).toBeTruthy()


	it 'should handle show all correctly', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('hide')
		@persistence.userSettingsReadShow = jasmine.createSpy('show')

		@scope.setShowAll(true)
		expect(@ShowAll.getShowAll()).toBeTruthy()
		expect(@persistence.userSettingsReadShow).toHaveBeenCalled()
		expect(@persistence.userSettingsReadHide).not.toHaveBeenCalled()


	it 'should handle hide all correctly', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('hide')
		@persistence.userSettingsReadShow = jasmine.createSpy('show')

		@scope.setShowAll(false)
		expect(@ShowAll.getShowAll()).toBeFalsy()
		expect(@persistence.userSettingsReadShow).not.toHaveBeenCalled()
		expect(@persistence.userSettingsReadHide).toHaveBeenCalled()


	it 'should get the correct count for starred items', =>
		@StarredCount.setStarredCount(133)
		count = @scope.getUnreadCount(@FeedType.Starred, 0)

		expect(count).toBe(133)


	it 'should set the count to 999+ if the count is over 999', =>
		@StarredCount.setStarredCount(1000)
		count = @scope.getUnreadCount(@FeedType.Starred, 0)

		expect(count).toBe('999+')


	it 'should get the correct unread count for feeds', =>
		@FeedModel.add({id: 3, unreadCount:134})
		count = @scope.getUnreadCount(@FeedType.Feed, 3)

		expect(count).toBe(134)


	it 'should get the correct unread count for subscribtions', =>
		@FeedModel.add({id: 3, unreadCount:134})
		@FeedModel.add({id: 5, unreadCount:2})
		count = @scope.getUnreadCount(@FeedType.Subscriptions, 0)

		expect(count).toBe(136)


	it 'should get the correct unread count for folders', =>
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 5})
		@FeedModel.add({id: 2, unreadCount:35, folderId: 3})
		count = @scope.getUnreadCount(@FeedType.Folder, 3)

		expect(count).toBe(169)


	it 'should reset the item cache when a different feed is being loaded', =>
		@ItemModel.clear = jasmine.createSpy('clear')
		@ActiveFeed.handle({id: 3, type: 3})
		@scope.loadFeed(3, 3)

		expect(@ItemModel.clear).not.toHaveBeenCalled()
		
		@scope.loadFeed(3, 4)
		expect(@ItemModel.clear).toHaveBeenCalled()


	it 'should send a get latest items query when feed did not change', =>
		@ItemModel.add({id: 1, lastModified: 5})
		@ItemModel.add({id: 2, lastModified: 1})
		@ItemModel.add({id: 4, lastModified: 323})
		@ItemModel.add({id: 6, lastModified: 44})
		@persistence.getItems = jasmine.createSpy('latest')
		@ActiveFeed.handle({id: 3, type: 3})
		@scope.loadFeed(3, 3)

		expect(@persistence.getItems).toHaveBeenCalledWith(3, 3, 0, null, 323)


	it 'should send a get all items query when feed changed', =>
		@persistence.getItems = jasmine.createSpy('latest')
		@ActiveFeed.handle({id: 3, type: 3})
		@scope.loadFeed(4, 3)

		expect(@persistence.getItems).toHaveBeenCalledWith(4, 3, 0)


	it 'should set active feed to new feed if changed', =>
		@ActiveFeed.handle({id: 3, type: 3})
		@scope.loadFeed(4, 3)

		expect(@ActiveFeed.getId()).toBe(3)
		expect(@ActiveFeed.getType()).toBe(4)


	it 'should return true when calling isShown and ShowAll is set to true', =>
		@ShowAll.setShowAll(true)
		expect(@scope.isShown(3, 4)).toBeTruthy()

		@ShowAll.setShowAll(false)
		expect(@scope.isShown(3, 4)).toBeFalsy()


	it 'should return true if ShowAll is false but unreadcount is not 0', =>
		@ShowAll.setShowAll(false)
		@FeedModel.add({id: 4, unreadCount: 0})
		expect(@scope.isShown(@FeedType.Feed, 4)).toBeFalsy()

		@FeedModel.add({id: 4, unreadCount: 12})
		expect(@scope.isShown(@FeedType.Feed, 4)).toBeTruthy()


	it 'should return all feeds of a folder', =>
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 5})
		@FeedModel.add({id: 2, unreadCount:35, folderId: 3})

		result = @scope.getFeedsOfFolder(3)

		expect(result).toContain(@FeedModel.getById(3))
		expect(result).toContain(@FeedModel.getById(2))
		expect(result).not.toContain(@FeedModel.getById(1))
		expect(result).not.toContain(@FeedModel.getById(5))


	it 'should return true when folder has feeds', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2})
		expect(@scope.hasFeeds(3)).toBeFalsy()

		@FeedModel.add({id: 2, unreadCount:35, folderId: 3})
		expect(@scope.hasFeeds(3)).toBeTruthy()		