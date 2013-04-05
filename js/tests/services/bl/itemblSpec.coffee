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


describe 'ItemBl', ->


	beforeEach module 'News'

	beforeEach =>
		angular.module('News').factory 'Persistence', =>
			@persistence = {}

	beforeEach inject (@ItemModel, @ItemBl, @StatusFlag, @ActiveFeed
	                   @FeedType, @FeedModel, @StarredBl) =>
		@item1 = {id: 5, title: 'hi', unreadCount:134, urlHash: 'a3', folderId: 3}
		@FeedModel.add(@item1)


	it 'should return all items', =>
		item1 = {id: 6, feedId: 5, guidHash: 'a1'}
		item2 = {id: 3, feedId: 5, guidHash: 'a2'}
		item3 = {id: 2, feedId: 5, guidHash: 'a3'}

		@ItemModel.add(item1)
		@ItemModel.add(item2)
		@ItemModel.add(item3)

		items = @ItemBl.getAll()

		expect(items).toContain(item1)
		expect(items).toContain(item2)
		expect(items).toContain(item3)


	it 'should tell if no feed is active', =>
		@ActiveFeed.handle({type: @FeedType.Folder, id: 0})
		expect(@ItemBl.noFeedActive()).toBe(true)

		@ActiveFeed.handle({type: @FeedType.Subscriptions, id: 0})
		expect(@ItemBl.noFeedActive()).toBe(true)

		@ActiveFeed.handle({type: @FeedType.Starred, id: 0})
		expect(@ItemBl.noFeedActive()).toBe(true)

		@ActiveFeed.handle({type: @FeedType.Shared, id: 0})
		expect(@ItemBl.noFeedActive()).toBe(true)

		@ActiveFeed.handle({type: @FeedType.Feed, id: 0})
		expect(@ItemBl.noFeedActive()).toBe(false)


	it 'should return the correct feed title', =>
		item2 = {id: 2, feedId: 5, guidHash: 'a3'}
		@ItemModel.add(item2)

		expect(@ItemBl.getFeedTitle(2)).toBe('hi')


	it 'should set an item unstarred', =>
		@persistence.unstarItem = jasmine.createSpy('star item')
		
		item2 = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item2)
		item2.setStarred()

		@ItemBl.toggleStarred(2)

		expect(item2.isStarred()).toBe(false)
		expect(@StarredBl.getUnreadCount()).toBe(-1)
		expect(@persistence.unstarItem).toHaveBeenCalledWith(5, 'a3')


	it 'should set an item starred', =>
		@persistence.starItem = jasmine.createSpy('unstar item')
		
		item2 = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item2)
		item2.setUnstarred()

		@ItemBl.toggleStarred(2)

		expect(item2.isStarred()).toBe(true)
		expect(@StarredBl.getUnreadCount()).toBe(1)
		expect(@persistence.starItem).toHaveBeenCalledWith(5, 'a3')


	it 'should set an item read', =>
		@persistence.readItem = jasmine.createSpy('read item')
		
		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setUnread()

		@ItemBl.setRead(2)

		expect(item.isRead()).toBe(true)
		expect(@persistence.readItem).toHaveBeenCalledWith(2)


	it 'should no set an item read if its already read', =>
		@persistence.readItem = jasmine.createSpy('read item')
		
		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setRead()

		@ItemBl.setRead(2)
		expect(@persistence.readItem).not.toHaveBeenCalled()


	it 'should return false when item kept unread does not exist', =>
		expect(@ItemBl.isKeptUnread(2)).toBe(false)


	it 'should return false if an item is not kept unread', =>
		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)

		expect(@ItemBl.isKeptUnread(2)).toBe(false)


	it 'should toggle an item as kept unread', =>
		@persistence.unreadItem = jasmine.createSpy('unread item')

		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)

		expect(@ItemBl.isKeptUnread(2)).toBe(false)

		@ItemBl.toggleKeepUnread(2)
		expect(@ItemBl.isKeptUnread(2)).toBe(true)

		@ItemBl.toggleKeepUnread(2)
		expect(@ItemBl.isKeptUnread(2)).toBe(false)


	it 'should set an item as unread', =>
		@persistence.unreadItem = jasmine.createSpy('unread item')

		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setRead()

		@ItemBl.setUnread(2)

		expect(item.isRead()).toBe(false)
		expect(@persistence.unreadItem).toHaveBeenCalledWith(2)


	it 'should not set an item as unread if its unread', =>
		@persistence.unreadItem = jasmine.createSpy('unread item')

		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setUnread()

		@ItemBl.setUnread(2)

		expect(item.isRead()).toBe(false)
		expect(@persistence.unreadItem).not.toHaveBeenCalled()


	it 'should set item as unread if kept unread is toggled and it is read', =>
		@persistence.unreadItem = jasmine.createSpy('unread item')

		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setRead()

		@ItemBl.toggleKeepUnread(2)

		expect(item.isRead()).toBe(false)
		expect(@persistence.unreadItem).toHaveBeenCalledWith(2)


	it 'should lower the unread count of a feed when its items get read', =>
		@persistence.readItem = jasmine.createSpy('read item')
		
		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setUnread()

		@ItemBl.setRead(2)

		expect(@item1.unreadCount).toBe(133)


	it 'should increase the unread count of a feed when its items get unread', =>
		@persistence.unreadItem = jasmine.createSpy('unread item')
		
		item = {id: 2, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item)
		item.setRead()

		@ItemBl.setUnread(2)

		expect(@item1.unreadCount).toBe(135)