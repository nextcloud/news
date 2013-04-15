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


describe 'FeedBusinessLayer', ->

	beforeEach module 'News'

	beforeEach =>
		angular.module('News').factory 'Persistence', =>
			@setFeedReadSpy = jasmine.createSpy('setFeedRead')
			@getItemsSpy = jasmine.createSpy('Get Items')
			@persistence = {
				setFeedRead: @setFeedReadSpy
				getItems: @getItemsSpy
				createFeed: ->
			}
		angular.module('News').factory 'Utils', =>
			@imagePath = jasmine.createSpy('imagePath')
			@utils = {
				imagePath: @imagePath
			}


	beforeEach inject (@FeedBusinessLayer, @FeedModel, @ItemModel, @FeedType,
	                   @ShowAll, @ActiveFeed, @_ExistsError) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Folder, id:0})

	it 'should delete feeds', =>
		@FeedModel.removeById = jasmine.createSpy('remove')
		@persistence.deleteFeed = jasmine.createSpy('deletequery')
		@FeedBusinessLayer.delete(3)

		expect(@FeedModel.removeById).toHaveBeenCalledWith(3)
		expect(@persistence.deleteFeed).toHaveBeenCalledWith(3)
		

	it 'should return the number of unread feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		count = @FeedBusinessLayer.getUnreadCount(3)

		expect(count).toBe(134)


	it 'should return all feeds of a folder', =>
		feed1 = {id: 3, unreadCount:134, urlHash: 'a1', folderId: 3}
		feed2 = {id: 4, unreadCount:134, urlHash: 'a2', folderId: 2}
		feed3 = {id: 5, unreadCount:134, urlHash: 'a3', folderId: 3}
		@FeedModel.add(feed1)
		@FeedModel.add(feed2)
		@FeedModel.add(feed3)

		feeds = @FeedBusinessLayer.getFeedsOfFolder(3)

		expect(feeds).toContain(feed1)
		expect(feeds).toContain(feed3)


	it 'should get the correct unread count for folders', =>
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 5, urlHash: 'a3'})
		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, urlHash: 'a4'})
		count = @FeedBusinessLayer.getFolderUnreadCount(3)

		expect(count).toBe(169)


	it 'should mark feed as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a1'})
		@ItemModel.add({id: 6, feedId: 5, guidHash: 'a1'})
		@ItemModel.add({id: 3, feedId: 5, guidHash: 'a2'})
		@ItemModel.add({id: 2, feedId: 5, guidHash: 'a3'})
		@FeedBusinessLayer.markFeedRead(5)

		expect(@persistence.setFeedRead).toHaveBeenCalledWith(5, 6)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)
		expect(@ItemModel.getById(6).isRead()).toBeTruthy()
		expect(@ItemModel.getById(3).isRead()).toBeTruthy()
		expect(@ItemModel.getById(2).isRead()).toBeTruthy()


	it 'should mark all as read', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, urlHash: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, urlHash: 'a3'})

		@FeedBusinessLayer.markAllRead()

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)


	it 'should get the correct unread count for subscribtions', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, urlHash: 'a2'})
		count = @FeedBusinessLayer.getAllUnreadCount()

		expect(count).toBe(136)


	it 'should return the correct number of feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, urlHash: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, urlHash: 'a2'})
		count = @FeedBusinessLayer.getNumberOfFeeds()

		expect(count).toBe(2)


	it 'should be visible if its active', =>
		@ActiveFeed.handle({type: @FeedType.Feed, id:3})
		expect(@FeedBusinessLayer.isVisible(3)).toBe(true)


	it 'should be visible if show all is true', =>
		expect(@FeedBusinessLayer.isVisible(3)).toBe(false)

		@ShowAll.setShowAll(true)
		expect(@FeedBusinessLayer.isVisible(3)).toBe(true)


	it 'should be visible if unreadcount bigger than 0', =>
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1'})
		expect(@FeedBusinessLayer.isVisible(2)).toBe(true)

	
	it 'should not move the feed to a new folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1', folderId: 3})
		@FeedBusinessLayer.move(2, 4)

		expect(@persistence.moveFeed).toHaveBeenCalledWith(2, 4)
		expect(@FeedModel.getById(2).folderId).toBe(4)


	it 'should not move the feed to the same folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, urlHash: 'a1', folderId: 3})
		@FeedBusinessLayer.move(2, 3)

		expect(@persistence.moveFeed).not.toHaveBeenCalled()


	it 'should set the show all setting', =>
		@persistence.userSettingsReadShow = jasmine.createSpy('Show All')
		@FeedBusinessLayer.setShowAll(true)

		expect(@persistence.userSettingsReadShow).toHaveBeenCalled()



	it 'should set the hide read setting', =>
		@persistence.userSettingsReadHide = jasmine.createSpy('Hide Read')
		@FeedBusinessLayer.setShowAll(false)

		expect(@persistence.userSettingsReadHide).toHaveBeenCalled()


	it 'should return all feeds', =>
		item1 = {id: 2, unreadCount:134, urlHash: 'a1', folderId: 3}
		item2 = {id: 4, unreadCount:134, urlHash: 'a2', folderId: 3}
		@FeedModel.add(item1)
		@FeedModel.add(item2)

		expect(@FeedBusinessLayer.getAll()).toContain(item1)
		expect(@FeedBusinessLayer.getAll()).toContain(item2)


	it 'should return if ShowAll is set', =>
		@persistence.userSettingsReadShow = jasmine.createSpy('Show All')
		expect(@FeedBusinessLayer.isShowAll()).toBe(false)
		@FeedBusinessLayer.setShowAll(true)

		expect(@FeedBusinessLayer.isShowAll()).toBe(true)


	it 'should return all feeds of a folder', =>
		item1 = {id: 2, unreadCount:134, urlHash: 'a1', folderId: 3}
		item2 = {id: 4, unreadCount:134, urlHash: 'a2', folderId: 2}
		item3 = {id: 5, unreadCount:134, urlHash: 'a3', folderId: 3}
		@FeedModel.add(item1)
		@FeedModel.add(item2)
		@FeedModel.add(item3)

		folders = @FeedBusinessLayer.getFeedsOfFolder(3)

		expect(folders).toContain(item1)
		expect(folders).toContain(item3)


	it 'should return the correct feed link', =>
		item2 =
			id: 4,
			unreadCount:134,
			urlHash: 'a2',
			folderId: 3,
			link: 'test.com'
		@FeedModel.add(item2)

		expect(@FeedBusinessLayer.getFeedLink(4)).toBe('test.com')



	it 'should not create a feed if it already exists', =>
		item1 = {urlHash: hex_md5('john')}
		@FeedModel.add(item1)

		expect =>
			@FeedBusinessLayer.create('john')
		.toThrow(new @_ExistsError())
		
		expect =>
			@FeedBusinessLayer.create('johns')
		.not.toThrow(new @_ExistsError())


	it 'should not create feeds that are empty', =>
		expect =>
			@FeedBusinessLayer.create('   ')
		.toThrow(new Error())


	it 'should create a feed before theres a response from the server', =>
		@FeedBusinessLayer.create('johns')
		expect(@FeedModel.size()).toBe(1)


	it 'should set a title and an url hash to the newly crated feed', =>
		url = 'www.google.de'
		@FeedBusinessLayer.create(url)
		hash = hex_md5(url)

		feed = @FeedModel.getByUrlHash(hash)

		expect(feed.title).toBe('google.de')
		expect(feed.url).toBe(url)
		expect(feed.urlHash).toBe(hash)
		expect(feed.folderId).toBe(0)
		expect(feed.unreadCount).toBe(0)
		expect(@imagePath).toHaveBeenCalledWith('core', 'loading.gif')
	
	it 'should transform urls correctly', =>
		urls = [
			'www.google.de'
			'www.google.de/'
			'google.de'
			'http://google.de'
			'http://www.google.de/'
		]
		for url in urls
			@FeedModel.clear()
			@FeedBusinessLayer.create(url)
			hash = hex_md5(url)
			feed = @FeedModel.getByUrlHash(hash)
			expect(feed.title).toBe('google.de')


	it 'should make a create feed request', =>
		@persistence.createFeed = jasmine.createSpy('add feed')
		
		@FeedBusinessLayer.create(' johns ')
		expect(@persistence.createFeed).toHaveBeenCalledWith('johns', 0,
			jasmine.any(Function))


	it 'should call the onSuccess function on response status ok', =>
		onSuccess = jasmine.createSpy('Success')
		@persistence.createFeed = jasmine.createSpy('add feed')
		@persistence.createFeed.andCallFake (folderName, parentId, success) =>
			@response =
				status: 'ok'
				data: 'hi'
			success(@response)

		@FeedBusinessLayer.create(' johns ', 0, onSuccess)

		expect(onSuccess).toHaveBeenCalledWith(@response.data)


	it 'should call the handle a response error when creating a folder', =>
		onSuccess = jasmine.createSpy('Success')
		onFailure = jasmine.createSpy('Failure')
		@persistence.createFeed = jasmine.createSpy('add feed')
		@persistence.createFeed.andCallFake (folderName, parentId, success) =>
			@response =
				status: 'error'
				msg: 'this is an error'
			success(@response)

		@FeedBusinessLayer.create(' johns ', 0, onSuccess, onFailure)

		expect(onSuccess).not.toHaveBeenCalled()
		expect(onFailure).toHaveBeenCalled()

		expect(@FeedModel.getByUrlHash(hex_md5('johns')).error).toBe(
			@response.msg)


	it 'should mark a feed error as read by removing it', =>
		@FeedModel.add({id: 3, urlHash: 'john'})

		@FeedBusinessLayer.markErrorRead('john')

		expect(@FeedModel.size()).toBe(0)
		expect(@FeedModel.getByUrlHash('john')).toBe(undefined)


	it 'should update all feeds', =>
		@persistence.updateFeed = jasmine.createSpy('update')
		@FeedModel.add({id: 3, urlHash: 'john'})

		@FeedBusinessLayer.updateFeeds()

		expect(@persistence.updateFeed).toHaveBeenCalledWith(3)


	it 'should not update feeds without ids', =>
		@persistence.updateFeed = jasmine.createSpy('update')
		@FeedModel.add({urlHash: 'john'})

		@FeedBusinessLayer.updateFeeds()

		expect(@persistence.updateFeed).not.toHaveBeenCalled()