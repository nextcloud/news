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

describe 'ItemModel', ->

	beforeEach module 'News'

	beforeEach inject (@ItemModel, @_Model) =>


	it 'should extend model', =>
		expect(@ItemModel instanceof @_Model).toBeTruthy()


	it 'should also update items with the same feed id and guidhash', =>
		item1 = {id: 4, guidHash: 'abc', feedId: 3}
		@ItemModel.add(item1)

		expect(@ItemModel.getById(4)).toBe(item1)

		# normal id update
		item2 = {id: 4, guidHash: 'abc', feedId: 4}
		@ItemModel.add(item2)
		expect(@ItemModel.size()).toBe(1)

		# new feeds should be added normally if different
		item3 = {id: 5, guidHash: 'abc', feedId: 6}
		@ItemModel.add(item3)
		expect(@ItemModel.size()).toBe(2)

		# feed should be updated when guidhash and feedid the same
		item4 = {id: 3, guidHash: 'abc', feedId: 6}
		@ItemModel.add(item4)
		expect(@ItemModel.getById(3).guidHash).toBe(item4.guidHash)
		expect(@ItemModel.getById(3).feedId).toBe(item4.feedId)
		expect(@ItemModel.getById(3).id).toBe(item4.id)
		expect(@ItemModel.getById(5)).toBe(undefined)
		expect(@ItemModel.size()).toBe(2)


	it 'should also remove the feed from the url cache when its removed', =>
		item = {id: 4, guidHash: 'abc', feedId: 3}
		@ItemModel.add(item)

		expect(@ItemModel.getById(4)).toBe(item)
		expect(@ItemModel.getByGuidHashAndFeedId('abc', 3)).toBe(item)

		@ItemModel.removeById(4)
		expect(@ItemModel.getByGuidHashAndFeedId('abc', 3)).toBe(undefined)


	it 'should bind the correct isRead() method to the item', =>
		item = {id: 3, guidHash: 'abc', feedId: 6, status: 16}

		@ItemModel.add(item)
		item.setRead()

		expect(@ItemModel.getById(3).isRead()).toBe(true)


	it 'should bind the correct set unread method to the item', =>
		item = {id: 3, guidHash: 'abc', feedId: 6, status: 16}

		@ItemModel.add(item)
		item.setUnread()

		expect(@ItemModel.getById(3).isRead()).toBe(false)


	it 'should bind the correct set starred method to the item', =>
		item = {id: 3, guidHash: 'abc', feedId: 6, status: 16}

		@ItemModel.add(item)
		item.setStarred()

		expect(@ItemModel.getById(3).isStarred()).toBe(true)


	it 'should bind the correct set unstarred method to the item', =>
		item = {id: 3, guidHash: 'abc', feedId: 6, status: 16}

		@ItemModel.add(item)
		item.setUnstarred()

		expect(@ItemModel.getById(3).isStarred()).toBe(false)


	it 'should return the lowest id', =>
		@ItemModel.add({id: 2, guidHash: 'abc', feedId: 2, status: 16})
		@ItemModel.add({id: 3, guidHash: 'abcd', feedId: 2, status: 16})
		@ItemModel.add({id: 1, guidHash: 'abce', feedId: 2, status: 16})
		@ItemModel.add({id: 6, guidHash: 'abcf', feedId: 2, status: 16})

		expect(@ItemModel.getLowestId()).toBe(1)