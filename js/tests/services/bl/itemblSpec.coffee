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
			@persistence =
				getItems: ->

	beforeEach inject (@ItemModel, @ItemBl, @StatusFlag) =>


	it 'should mark all items read of a feed', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		item1 = {id: 6, feedId: 5, guidHash: 'a1', status: @StatusFlag.UNREAD}
		item2 = {id: 3, feedId: 5, guidHash: 'a2', status: @StatusFlag.UNREAD}
		item3 = {id: 2, feedId: 5, guidHash: 'a3', status: @StatusFlag.UNREAD}
		@ItemModel.add(item1)
		@ItemModel.add(item2)
		@ItemModel.add(item3)
		@ItemBl.markAllRead(5)

		expect(@persistence.setFeedRead).toHaveBeenCalledWith(5, 6)
		expect(item1.isRead()).toBe(true)
		expect(item2.isRead()).toBe(true)
		expect(item3.isRead()).toBe(true)