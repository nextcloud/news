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
			@setFeedReadSpy = jasmine.createSpy('setFeedRead')
			@persistence = {
				
			}

	beforeEach inject (@ItemModel, @ItemBl, @StatusFlag, @ActiveFeed
	                   @FeedType) =>


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

