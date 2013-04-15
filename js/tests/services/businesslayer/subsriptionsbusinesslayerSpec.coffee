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


describe 'SubscriptionsBusinessLayer', ->

	beforeEach module 'News'

	beforeEach =>
		angular.module('News').factory 'Persistence', =>
			@setFeedReadSpy = jasmine.createSpy('setFeedRead')
			@persistence = {
				setFeedRead: @setFeedReadSpy
			}

	beforeEach inject (@SubscriptionsBusinessLayer, @ShowAll, @FeedModel,
	                   @ActiveFeed, @FeedType) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Feed, id:0})


	it 'should be visible shows all items is set to true and there are feeds', =>
		@FeedModel.add({id: 3, unreadCount: 5})

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


	it 'should always be visible if its the active feed', =>
		@ActiveFeed.handle({type: @FeedType.Subscriptions, id:0})
		expect(@SubscriptionsBusinessLayer.isVisible()).toBe(true)


	it 'should mark all feeds as read', =>
		item = {id: 3, unreadCount: 132}
		@FeedModel.add(item)

		@SubscriptionsBusinessLayer.markAllRead()

		expect(item.unreadCount).toBe(0)
		expect(@setFeedReadSpy).toHaveBeenCalled()


	it 'should get the correct unread count', =>
		@FeedModel.add({id: 3, unreadCount: 132, urlHash: 'hoho'})
		@FeedModel.add({id: 4, unreadCount: 12, urlHash: 'hohod'})

		expect(@SubscriptionsBusinessLayer.getUnreadCount()).toBe(144)


