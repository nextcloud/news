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

	beforeEach module ($provide) =>
		@setFeedReadSpy = jasmine.createSpy('setFeedRead')
		@getItemsSpy = jasmine.createSpy('Get Items')
		@persistence =
			setFeedRead: @setFeedReadSpy
			getItems: @getItemsSpy
			createFeed: ->
			test: 'feedbusinesslayer'

		@imagePath = jasmine.createSpy('imagePath')
		@utils =
			imagePath: @imagePath

		$provide.value 'Persistence', @persistence
		$provide.value 'Utils', @utils
		return

	
	beforeEach inject (@FeedBusinessLayer, @FeedModel, @ItemModel, @FeedType,
	                   @ShowAll, @ActiveFeed, @_ExistsError, @$timeout,
	                   @NewestItem) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Folder, id:0})

	it 'should delete feeds', =>
		@FeedModel.removeById = jasmine.createSpy('remove').andCallFake ->
			return {id: 3, title: 'test'}
		@persistence.deleteFeed = jasmine.createSpy('deletequery')
		@FeedBusinessLayer.delete(3)

		expect(@FeedModel.removeById).toHaveBeenCalledWith(3)
		
		@$timeout.flush()

		expect(@persistence.deleteFeed).toHaveBeenCalledWith(3)
		

	it 'should return the number of unread feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, url: 'a1'})
		count = @FeedBusinessLayer.getUnreadCount(3)

		expect(count).toBe(134)


	it 'should return all feeds of a folder', =>
		feed1 = {id: 3, unreadCount:134, url: 'a1', folderId: 3}
		feed2 = {id: 4, unreadCount:134, url: 'a2', folderId: 2}
		feed3 = {id: 5, unreadCount:134, url: 'a3', folderId: 3}
		@FeedModel.add(feed1)
		@FeedModel.add(feed2)
		@FeedModel.add(feed3)

		feeds = @FeedBusinessLayer.getFeedsOfFolder(3)

		expect(feeds).toContain(feed1)
		expect(feeds).toContain(feed3)


	it 'should get the correct unread count for folders', =>
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, url: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 5, url: 'a3'})
		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, url: 'a4'})
		count = @FeedBusinessLayer.getFolderUnreadCount(3)

		expect(count).toBe(169)


	it 'should not mark feed read when no highest item id', =>
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedBusinessLayer.markRead(5)
		expect(@persistence.setFeedRead).not.toHaveBeenCalled()


	it 'should mark feed as read', =>
		@NewestItem.handle(25)
		@ActiveFeed.handle({type: @FeedType.Feed, id: 5})
		@persistence.setFeedRead = jasmine.createSpy('setFeedRead')
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a1'})
		@ItemModel.add({id: 6, feedId: 5, guidHash: 'a1'})
		@ItemModel.add({id: 3, feedId: 5, guidHash: 'a2'})
		@ItemModel.add({id: 2, feedId: 5, guidHash: 'a3'})
		@FeedBusinessLayer.markRead(5)

		expect(@persistence.setFeedRead).toHaveBeenCalledWith(5, 25)
		expect(@FeedModel.getById(5).unreadCount).toBe(0)
		expect(@ItemModel.getById(6).isRead()).toBeTruthy()
		expect(@ItemModel.getById(3).isRead()).toBeTruthy()
		expect(@ItemModel.getById(2).isRead()).toBeTruthy()


	it 'should get the correct unread count for subscribtions', =>
		@FeedModel.add({id: 3, unreadCount:134, url: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, url: 'a2'})
		count = @FeedBusinessLayer.getAllUnreadCount()

		expect(count).toBe(136)


	it 'should return the correct number of feeds', =>
		@FeedModel.add({id: 3, unreadCount:134, url: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, url: 'a2'})
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
		@FeedModel.add({id: 2, unreadCount:134, url: 'a1'})
		expect(@FeedBusinessLayer.isVisible(2)).toBe(true)

	
	it 'should not move the feed to a new folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, url: 'a1', folderId: 3})
		@FeedBusinessLayer.move(2, 4)

		expect(@persistence.moveFeed).toHaveBeenCalledWith(2, 4)
		expect(@FeedModel.getById(2).folderId).toBe(4)


	it 'should not move the feed to the same folder', =>
		@persistence.moveFeed = jasmine.createSpy('Move feed')
		@FeedModel.add({id: 2, unreadCount:134, url: 'a1', folderId: 3})
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
		item1 = {id: 2, unreadCount:134, url: 'a1', folderId: 3}
		item2 = {id: 4, unreadCount:134, url: 'a2', folderId: 3}
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
		item1 = {id: 2, unreadCount:134, url: 'a1', folderId: 3}
		item2 = {id: 4, unreadCount:134, url: 'a2', folderId: 2}
		item3 = {id: 5, unreadCount:134, url: 'a3', folderId: 3}
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
			url: 'a2',
			folderId: 3,
			link: 'test.com'
		@FeedModel.add(item2)

		expect(@FeedBusinessLayer.getFeedLink(4)).toBe('test.com')



	it 'should not create a feed if it already exists', =>
		item1 = {url: 'http://john'}
		@FeedModel.add(item1)

		expect =>
			@FeedBusinessLayer.create('john')
		.toThrow(new @_ExistsError('Exists already'))
		
		expect =>
			@FeedBusinessLayer.create('johns')
		.not.toThrow(new @_ExistsError('Exists already'))


	it 'should not create feeds that are empty', =>
		expect =>
			@FeedBusinessLayer.create('   ')
		.toThrow(new Error('Url must not be empty'))


	it 'should create a feed before theres a response from the server', =>
		@FeedBusinessLayer.create('johns')
		expect(@FeedModel.size()).toBe(1)


	it 'should set a title and an url to the newly created feed', =>
		url = 'www.google.de'
		@FeedBusinessLayer.create(url)

		feed = @FeedModel.getByUrl('http://' + url)

		expect(feed.title).toBe('http://www.google.de')
		expect(feed.url).toBe('http://' + url)
		expect(feed.folderId).toBe(0)
		expect(feed.unreadCount).toBe(0)
		expect(@imagePath).toHaveBeenCalledWith('core', 'loading.gif')
	

	it 'should not add http when it already is at the start of created feed', =>
		url = 'https://www.google.de'
		@FeedBusinessLayer.create(url)
		feed = @FeedModel.getByUrl(url)

		expect(feed.url).toBe(url)


	it 'should make a create feed request', =>
		@persistence.createFeed = jasmine.createSpy('add feed')
		
		@FeedBusinessLayer.create(' johns ')
		expect(@persistence.createFeed).toHaveBeenCalledWith('http://johns', 0,
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

		expect(@FeedModel.getByUrl('http://johns').error).toBe(
			@response.msg)


	it 'should mark a feed error as read by removing it', =>
		@FeedModel.add({id: 3, url: 'john'})

		@FeedBusinessLayer.markErrorRead('john')

		expect(@FeedModel.size()).toBe(0)
		expect(@FeedModel.getByUrl('john')).toBe(undefined)


	it 'should not import google reader json', =>
		@persistence.importGoogleReader = jasmine.createSpy('importGoogleReader')

		json = {"test": "hi"}
		@FeedBusinessLayer.importGoogleReader(json)

		imported = @FeedModel.getByUrl('http://owncloud/googlereader')
		expect(imported.title).toBe('Google Reader')
		expect(imported.folderId).toBe(0)
		expect(imported.unreadCount).toBe(0)


	it 'should not create a google reader feed if it already exists', =>
		@persistence.importGoogleReader = jasmine.createSpy('importGoogleReader')

		@FeedModel.add({id: 3, url: 'http://owncloud/googlereader'})
		json = {"test": "hi"}
		@FeedBusinessLayer.importGoogleReader(json)

		imported = @FeedModel.getByUrl('http://owncloud/googlereader')
		expect(imported.folderId).not.toBeDefined()


	it 'should create an import google reader request', =>
		returned =
			data:
				feeds: [
					{id: 3, url: 'hi'}
				]
		@persistence.getItems = jasmine.createSpy('importGoogleReader')
		@persistence.importGoogleReader = jasmine.createSpy('importGoogleReader')
		@persistence.importGoogleReader.andCallFake (data, onSuccess) =>
			@FeedModel.handle(returned.data.feeds)
			onSuccess(returned)

		json = {"test": "hi"}
		@FeedBusinessLayer.importGoogleReader(json)

		expect(@persistence.importGoogleReader).toHaveBeenCalledWith(json,
			jasmine.any(Function))
		expect(@persistence.getItems).toHaveBeenCalledWith(
			@FeedType.Feed, returned.data.feeds[0].id, 0
		)
		expect(@ActiveFeed.getId()).toBe(returned.data.feeds[0].id)
		expect(@ActiveFeed.getType()).toBe(@FeedType.Feed)

