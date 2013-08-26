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


describe 'SubscriptionsBusinessLayer', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@persistence =
			setFeedRead: jasmine.createSpy('setFeedRead')
			test: 'subscriptionsbusinesslayer'

		$provide.value 'Persistence', @persistence
		return

	beforeEach inject (@SubscriptionsBusinessLayer, @ShowAll, @FeedModel,
	                   @ActiveFeed, @FeedType, @NewestItem) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Feed, id:0})


	it 'should be visible shows all items is set to true and there are feeds', =>
		@FeedModel.add({id: 3, unreadCount: 5, url: 'hi'})

		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(true)

		@ShowAll.setShowAll(true)
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(true)


	it 'should not be visible if there are no feeds', =>
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(false)

		@ShowAll.setShowAll(true)
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(false)


	it 'should not be visible if showall is false + there are no unread', =>
		@FeedModel.add({id: 3, unreadCount: 0})
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(false)


	it 'should always be visible if its the active feed and there are feeds', =>
		@ActiveFeed.handle({type: @FeedType.Subscriptions, id:0})
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(false)

		@FeedModel.add({id: 3, unreadCount: 0, url: 'hi'})
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(true)


	it 'should mark all as read', =>
		@NewestItem.handle(25)
		@persistence.setAllRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, url: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, url: 'a3'})

		@SubscriptionsBusinessLayer.markRead()

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)
		expect(@persistence.setAllRead).toHaveBeenCalledWith(25)


	it 'should not mark all read when no highest item id', =>
		@persistence.setAllRead = jasmine.createSpy('setAllRead')
		@SubscriptionsBusinessLayer.markRead()
		expect(@persistence.setAllRead).not.toHaveBeenCalled()

	it 'should get the correct unread count', =>
		@FeedModel.add({id: 3, unreadCount: 132, url: 'hoho'})
		@FeedModel.add({id: 4, unreadCount: 12, url: 'hohod'})

		expect(@SubscriptionsBusinessLayer.getUnreadCount()).toBe(144)


