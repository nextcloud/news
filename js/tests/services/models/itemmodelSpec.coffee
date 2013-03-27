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

describe '_ItemModel', ->


	beforeEach module 'News'

	beforeEach inject (@_ItemModel, @_Model) =>


	it 'should extend model', =>
		expect(new @_ItemModel instanceof @_Model).toBeTruthy()


	it 'should also update items with the same feed id and guidhash', =>
		model = new @_ItemModel()
		item1 = {id: 4, guidHash: 'abc', feedId: 3}
		model.add(item1)

		expect(model.getById(4)).toBe(item1)

		# normal id update
		item2 = {id: 4, guidHash: 'abc', feedId: 4}
		model.add(item2)
		expect(model.size()).toBe(1)

		# new feeds should be added normally if different
		item3 = {id: 5, guidHash: 'abc', feedId: 6}
		model.add(item3)
		expect(model.size()).toBe(2)

		# feed should be updated when guidhash and feedid the same
		item4 = {id: 3, guidHash: 'abc', feedId: 6}
		model.add(item4)
		expect(model.getById(3).guidHash).toBe(item4.guidHash)
		expect(model.getById(3).feedId).toBe(item4.feedId)
		expect(model.getById(3).id).toBe(item4.id)
		expect(model.getById(5)).toBe(undefined)
		expect(model.size()).toBe(2)


	it 'should also remove the feed from the urlHash cache when its removed', =>
		model = new @_ItemModel()
		item = {id: 4, guidHash: 'abc', feedId: 3}
		model.add(item)

		expect(model.getById(4)).toBe(item)
		expect(model.getByGuidHashAndFeedId('abc', 3)).toBe(item)

		model.removeById(4)
		expect(model.getByGuidHashAndFeedId('abc', 3)).toBe(undefined)