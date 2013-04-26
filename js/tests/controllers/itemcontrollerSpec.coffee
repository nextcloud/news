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


describe 'ItemController', ->


	beforeEach module 'News'

	beforeEach module ($provide) =>
		@imagePath = jasmine.createSpy('imagePath')
		@utils =
			imagePath: @imagePath
		$provide.value 'Utils', @utils

		@persistence =
			getItems: ->
			readItem: ->
		$provide.value 'Persistence', @persistence
		return

	beforeEach inject ($controller, @ItemBusinessLayer, @FeedBusinessLayer,
		$rootScope, @FeedLoading, @AutoPageLoading, @FeedModel, @ItemModel,
		@ActiveFeed, @FeedType, @NewestItem) =>
		
		@ActiveFeed.handle({type: @FeedType.Folder, id: 3})
		@scope = $rootScope.$new()
		replace =
			$scope: @scope
		@controller = $controller('ItemController', replace)


	it 'should make ItemBusinessLayer availabe', =>
		expect(@scope.itemBusinessLayer).toBe(@ItemBusinessLayer)


	it 'should make FeedBusinessLayer availabe', =>
		expect(@scope.feedBusinessLayer).toBe(@FeedBusinessLayer)


	it 'should make feedloading available', =>
		expect(@scope.isLoading()).toBe(false)
		@FeedLoading.increase()
		expect(@scope.isLoading()).toBe(true)


	it 'should make autopagin available', =>
		expect(@scope.isAutoPaging()).toBe(false)
		@AutoPageLoading.increase()
		expect(@scope.isAutoPaging()).toBe(true)


	it 'should return the feedtitle', =>
		item = {id: 3, faviconLink: null, url: 'hi', title: 'heheh'}
		@FeedModel.add(item)

		expect(@scope.getFeedTitle(3)).toBe(item.title)


	it 'should return no value if feedtitle is not found', =>
		expect(@scope.getFeedTitle(3)).toBe('')


	it 'should return no value if relative date gets no value', =>
		expect(@scope.getRelativeDate()).toBe('')


	it 'should set an item read on readItem broadcast', =>
		item1 = {id: 4, guidHash: 'abc', feedId: 3}
		@ItemModel.add(item1)
		item1.setUnread()

		expect(item1.isRead()).toBe(false)
		@scope.$broadcast 'readItem', 4

		expect(item1.isRead()).toBe(true)


	it 'should not autopage if there are no items', =>
		@persistence.getItems = jasmine.createSpy('getItems')
		@scope.$broadcast 'autoPage'
		expect(@persistence.getItems).not.toHaveBeenCalled()


	it 'should autoPage with the newest Item Id', =>
		@NewestItem.handle(25)

		@persistence.getItems = jasmine.createSpy('getItems')
		item1 = {id: 4, guidHash: 'abc', feedId: 3}
		@ItemModel.add(item1)

		item1 = {id: 3, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)

		item1 = {id: 6, guidHash: 'abce', feedId: 1}
		@ItemModel.add(item1)

		@scope.$broadcast 'autoPage'
		expect(@persistence.getItems).toHaveBeenCalledWith(
			@FeedType.Folder, 3, 3, 25, jasmine.any(Function)
		)


	it 'should not prevent autopaging if there are no items', =>
		@NewestItem.handle(25)
		@scope.$broadcast 'autoPage'
		@persistence.getItems = jasmine.createSpy('getItems')

		item1 = {id: 3, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)

		@scope.$broadcast 'autoPage'
		expect(@persistence.getItems).toHaveBeenCalledWith(
			@FeedType.Folder, 3, 1, 25, jasmine.any(Function)
		)


	it 'should not send multiple autopage requests at once', =>
		@NewestItem.handle(25)
		@persistence.getItems = jasmine.createSpy('getItems')
		item1 = {id: 3, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)
		
		@scope.$broadcast 'autoPage'

		item1 = {id: 2, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)

		@scope.$broadcast 'autoPage'

		expect(@persistence.getItems).not.toHaveBeenCalledWith(
			@FeedType.Folder, 2, 1, 25, jasmine.any(Function)
		)


	it 'should allow another autopaging request if the last one finished', =>
		@NewestItem.handle(25)
		@persistence.getItems = jasmine.createSpy('getItems')
		@persistence.getItems.andCallFake (type, id, offset, newestItemId,
			onSuccess) ->
			onSuccess()

		item1 = {id: 3, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)
		
		@scope.$broadcast 'autoPage'

		item1 = {id: 2, guidHash: 'abcd', feedId: 3}
		@ItemModel.add(item1)

		@scope.$broadcast 'autoPage'

		expect(@persistence.getItems.callCount).toBe(2)

