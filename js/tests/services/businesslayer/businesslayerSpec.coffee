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


describe 'BusinessLayer', ->

	beforeEach module 'News'

	beforeEach inject (@_BusinessLayer, @ActiveFeed, @FeedType, @ItemModel,
		$rootScope) =>
		@scope = $rootScope.$new()

		type = @FeedType.Starred
		
		@getItemsSpy = jasmine.createSpy('getItems')
		@persistence = {
			getItems: @getItemsSpy
		}

		class TestBusinessLayer extends @_BusinessLayer

			constructor: (activeFeed, persistence, itemModel, scope) ->
				super(activeFeed, persistence, itemModel, type, scope)

		@BusinessLayer = new TestBusinessLayer(@ActiveFeed, @persistence,
		@ItemModel, @scope)


	it 'should reset the item cache when a different feed is being loaded', =>
		@ItemModel.clear = jasmine.createSpy('clear')
		@ActiveFeed.handle({id: 0, type: @FeedType.Starred})
		
		@BusinessLayer.load(2)
		expect(@ItemModel.clear).toHaveBeenCalled()

		@ActiveFeed.handle({id: 2, type: @FeedType.Feed})
		@BusinessLayer.load(2)
		expect(@ItemModel.clear).toHaveBeenCalled()



	it 'should send a get all items query when feed changed', =>
		@persistence.getItems = jasmine.createSpy('latest')
		@ActiveFeed.handle({id: 3, type: @FeedType.Feed})
		@BusinessLayer.load(3)

		expect(@persistence.getItems).toHaveBeenCalledWith(@FeedType.Starred, 3,
			0, jasmine.any(Function))


	it 'should be active when its selected', =>
		expect(@BusinessLayer.isActive(0)).toBe(false)

		@ActiveFeed.handle({type: @FeedType.Starred, id:0})
		expect(@BusinessLayer.isActive(0)).toBe(true)