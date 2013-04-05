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


describe 'FeedBl', ->

	beforeEach module 'News'

	beforeEach =>
		angular.module('News').factory 'Persistence', =>
			@setFeedReadSpy = jasmine.createSpy('setFeedRead')
			@persistence = {
				setFeedRead: @setFeedReadSpy
			}

	beforeEach inject (@FeedBl, @FeedModel, @ItemModel, @FeedType,
	                   @ShowAll, @ActiveFeed) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Folder, id:0})

	it 'should delete feeds', =>
		@FeedModel.removeById = jasmine.createSpy('remove')
		@persistence.deleteFeed = jasmine.createSpy('deletequery')
		@FeedBl.delete(3)

		expect(@FeedModel.removeById).toHaveBeenCalledWith(3)
		expect(@persistence.deleteFeed).toHaveBeenCalledWith(3)
		

	it 'should return the number of unread feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		count = @FeedBl.getUnreadCount(3)

		expect(count).toBe(134)


	it 'should return all feeds of a folder', =>
		feed1 = {id: 3, unreadCount:134, urlHash: 'a1', folderId: 3}
		feed2 = {id: 4, unreadCount:134, urlHash: 'a2', folderId: 2}
		feed3 = {id: 5, unreadCount:134, urlHash: 'a3', folderId: 3}
		@FeedModel.add(feed1)
		@FeedModel.add(feed2)
		@FeedModel.add(feed3)

		feeds = @FeedBl.getFeedsOfFolder(3)

		expect(feeds).toContain(feed1)
		expect(feeds).toContain(feed3)


	it 'should get the correct unread count for folders', =>
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 5, urlHash: 'a3'})
		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, urlHash: 'a4'})
		count = @FeedBl.getFolderUnreadCount(3)

		expect(count).toBe(169)


	it 'should mark feed as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		@ItemModel.add({id: 6, feedId: 5, guidHash: 'a1'})
		@ItemModel.add({id: 3, feedId: 5, guidHash: 'a2'})
		@ItemModel.add({id: 2, feedId: 5, guidHash: 'a3'})
		@FeedBl.markFeedRead(5)

		expect(@persistence.setFeedRead).toHaveBeenCalledWith(5, 6)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)
		expect(@ItemModel.getById(6).isRead()).toBeTruthy()
		expect(@ItemModel.getById(3).isRead()).toBeTruthy()
		expect(@ItemModel.getById(2).isRead()).toBeTruthy()


	it 'should mark all as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, urlHash: 'a3'})

		@FeedBl.markAllRead()

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)


	it 'should get the correct unread count for subscribtions', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, urlHash: 'a2'})
		count = @FeedBl.getAllUnreadCount()

		expect(count).toBe(136)


	it 'should return the correct number of feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, urlHash: 'a2'})
		count = @FeedBl.getNumberOfFeeds()

		expect(count).toBe(2)


	it 'should be visible if its active', =>
		@ActiveFeed.handle({type: @FeedType.Feed, id:3})
		expect(@FeedBl.isVisible(3)).toBe(true)


	it 'should be visible if show all is true', =>
		expect(@FeedBl.isVisible(3)).toBe(false)

		@ShowAll.setShowAll(true)
		expect(@FeedBl.isVisible(3)).toBe(true)


	it 'should be visible if unreadcount bigger than 0', =>
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1'})
		expect(@FeedBl.isVisible(2)).toBe(true)

	
	it 'should not move the feed to a new folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1', folderId: 3})
		@FeedBl.move(2, 4)

		expect(@persistence.moveFeed).toHaveBeenCalledWith(2, 4)
		expect(@FeedModel.getById(2).folderId).toBe(4)


	it 'should not move the feed to the same folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1', folderId: 3})
		@FeedBl.move(2, 3)

		expect(@persistence.moveFeed).not.toHaveBeenCalled()


	it 'should set the show all setting', =>
		@persistence.userSettingsReadShow = jasmine.createSpy('Show All')
		@FeedBl.setShowAll(true)

		expect(@persistence.userSettingsReadShow).toHaveBeenCalled()



	it 'should set the hide read setting', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('Hide Read')
		@FeedBl.setShowAll(false)

		expect(@persistence.userSettingsReadHide).toHaveBeenCalled()


	it 'should return all feeds', =>
		item1 = {id: 2, unreadCount:134, urlHash: 'a1', folderId: 3}
		item2 = {id: 4, unreadCount:134, urlHash: 'a2', folderId: 3}
		@FeedModel.add(item1)
		@FeedModel.add(item2)

		expect(@FeedBl.getAll()).toContain(item1)
		expect(@FeedBl.getAll()).toContain(item2)


	it 'should retunr if ShowAll is set', =>
		@persistence.userSettingsReadShow = jasmine.createSpy('Show All')
		expect(@FeedBl.isShowAll()).toBe(false)
		@FeedBl.setShowAll(true)

		expect(@FeedBl.isShowAll()).toBe(true)