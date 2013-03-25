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


describe '_FeedController', ->


	beforeEach module 'News'


	beforeEach inject (@_FeedController, @ActiveFeed, @ShowAll, @FeedType,
		               @StarredCount) =>
		@scope = {}
		@feedModel =
			getAll: ->
		@folderModel =
			getAll: ->
		@controller = new @_FeedController(@scope, @folderModel, @feedModel, @ActiveFeed,
			                 @ShowAll, @FeedType, @StarredCount)


	it 'should make folders available', =>
		@folderModel = 
			getAll: jasmine.createSpy('FolderModel')
		
		new @_FeedController(@scope, @folderModel, @feedModel, @_ActiveFeed)

		expect(@folderModel.getAll).toHaveBeenCalled()


	it 'should make feeds availabe', =>
		@feedModel = 
			getAll: jasmine.createSpy('FeedModel')
		
		new @_FeedController(@scope, @folderModel, @feedModel, @_ActiveFeed)

		expect(@feedModel.getAll).toHaveBeenCalled()		


	it 'should make feedtype available', =>
		expect(@scope.feedType).toBe(@FeedType)		


	it 'should check the active feed', =>
		@ActiveFeed.getType = =>
			return @FeedType.Feed
		@ActiveFeed.getId = =>
			return 5

		expect(@scope.isFeedActive(@FeedType.Feed, 5)).toBeTruthy()


	it 'should provide ShowAll', =>
		expect(@scope.isShowAll()).toBeFalsy()
		
		@ShowAll.handle(true)
		expect(@scope.isShowAll()).toBeTruthy()