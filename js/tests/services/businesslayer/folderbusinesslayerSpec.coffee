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


describe 'FolderBusinessLayer', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@persistence =
			test: 'folderbusinesslayer'

		@imagePath = jasmine.createSpy('imagePath')
		@utils =
			imagePath: @imagePath

		$provide.value 'Persistence', @persistence
		$provide.value 'Utils', @utils
		return


	beforeEach inject (@FolderBusinessLayer, @FolderModel,	@FeedModel, @ShowAll,
		               @ActiveFeed, @FeedType, @_ExistsError, @$timeout,
		               @NewestItem, @ItemModel) =>
		@ShowAll.setShowAll(false)
		@ActiveFeed.handle({type: @FeedType.Feed, id:0})


	it 'should delete folders', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 3, url: 'a1'})
		@FolderModel.removeById = jasmine.createSpy('remove').andCallFake ->
			return {id: 3, name: 'test'}
		@FeedModel.removeById = jasmine.createSpy('remove').andCallFake ->
			return {id: 5, name: 'test', folderId: 3}

		@persistence.deleteFolder = jasmine.createSpy('deletequery')
		@FolderBusinessLayer.delete(3)
		@$timeout.flush()

		expect(@FolderModel.removeById).toHaveBeenCalledWith(3)
		expect(@FeedModel.removeById).toHaveBeenCalledWith(5)
		expect(@persistence.deleteFolder).toHaveBeenCalledWith(3)


	it 'should return true when folder has feeds', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a1'})
		expect(@FolderBusinessLayer.hasFeeds(3)).toBeFalsy()

		@FeedModel.add({id: 2, unreadCount:35, folderId: 3, url: 'a2'})
		expect(@FolderBusinessLayer.hasFeeds(3)).toBeTruthy()


	it 'should toggle folder', =>
		@persistence.openFolder = jasmine.createSpy('open')
		@persistence.collapseFolder = jasmine.createSpy('collapse')

		@FolderModel.add({id: 3, opened: false, name: 'ho'})
		@FolderBusinessLayer.toggleFolder(4)
		expect(@FolderModel.getById(3).opened).toBeFalsy()

		@FolderBusinessLayer.toggleFolder(3)
		expect(@FolderModel.getById(3).opened).toBeTruthy()
		expect(@persistence.openFolder).toHaveBeenCalledWith(3)

		@FolderBusinessLayer.toggleFolder(3)
		expect(@FolderModel.getById(3).opened).toBeFalsy()
		expect(@persistence.collapseFolder).toHaveBeenCalledWith(3)


	it 'should mark folder as read', =>
		@NewestItem.handle(25)

		@persistence.setFolderRead = jasmine.createSpy('setFeedRead')
		
		item1 = {id: 3, feedId: 5, guidHash: 'a3', status: 0}
		@ItemModel.add(item1)
		item1.setUnread()
		item2 = {id: 2, feedId: 3, guidHash: 'a3', status: 0}
		@ItemModel.add(item2)
		item2.setUnread()

		@FolderModel.add({id: 3, opened: false, name: 'ho'})
		@FeedModel.add({id: 3, unreadCount:134, folderId: 3, url: 'a1'})
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a2'})
		@FeedModel.add({id: 1, unreadCount:12, folderId: 3, url: 'a3'})

		@FolderBusinessLayer.markRead(3)

		expect(@FeedModel.getById(3).unreadCount).toBe(0)
		expect(@FeedModel.getById(1).unreadCount).toBe(0)
		expect(@FeedModel.getById(5).unreadCount).toBe(2)

		expect(item1.isRead()).toBe(false)
		expect(item2.isRead()).toBe(true)

		expect(@persistence.setFolderRead).toHaveBeenCalledWith(3, 25)


	it 'should not mark folder read when no highest item id', =>
		@FolderModel.add({id: 5, opened: false, name: 'ho'})
		@persistence.setFolderRead = jasmine.createSpy('setFolderRead')
		@FolderBusinessLayer.markRead(5)
		expect(@persistence.setFolderRead).not.toHaveBeenCalled()


	it 'should get the correct unread count', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a1'})
		@FeedModel.add({id: 6, unreadCount:3, folderId: 3, url: 'a2'})
		@FeedModel.add({id: 7, unreadCount:4, folderId: 2, url: 'a3'})

		expect(@FolderBusinessLayer.getUnreadCount(2)).toBe(6)


	it 'should be visible if show all is true', =>
		expect(@FolderBusinessLayer.isVisible(3)).toBe(false)

		@ShowAll.setShowAll(true)
		expect(@FolderBusinessLayer.isVisible(3)).toBe(true)


	it 'should be visible if its active', =>
		@ActiveFeed.handle({type: @FeedType.Folder, id:3})
		expect(@FolderBusinessLayer.isVisible(3)).toBe(true)


	it 'should be visible if one of its subfeeds is active', =>
		@FeedModel.add({id: 5, unreadCount:0, folderId: 2, url: 'a1'})
		@FeedModel.add({id: 6, unreadCount:0, folderId: 3, url: 'a2'})
		@FeedModel.add({id: 7, unreadCount:0, folderId: 2, url: 'a3'})

		@ActiveFeed.handle({type: @FeedType.Feed, id:6})
		expect(@FolderBusinessLayer.isVisible(3)).toBe(true)


	it 'should be visible if showAll is false and it has unread items', =>
		@FeedModel.add({id: 5, unreadCount:2, folderId: 2, url: 'a1'})
		@FeedModel.add({id: 6, unreadCount:3, folderId: 3, url: 'a2'})
		@FeedModel.add({id: 7, unreadCount:4, folderId: 2, url: 'a3'})

		@ActiveFeed.handle({type: @FeedType.Folder, id:2})
		expect(@FolderBusinessLayer.isVisible(3)).toBe(true)


	it 'should return all folders', =>
		item1 = {id: 3, open: false, name: 'ho'}
		item2 = {id: 4, open: true, name: 'hod'}
		@FolderModel.add(item1)
		@FolderModel.add(item2)

		expect(@FolderBusinessLayer.getAll()).toContain(item1)
		expect(@FolderBusinessLayer.getAll()).toContain(item2)


	it 'should not create a folder if it already exists', =>
		item1 = {id: 4, open: true, name: 'john'}
		@FolderModel.add(item1)

		expect =>
			@FolderBusinessLayer.create('john')
		.toThrow(new @_ExistsError('Exists already'))
		
		expect =>
			@FolderBusinessLayer.create('johns')
		.not.toThrow(new @_ExistsError('Exists already'))


	it 'should not create folders that are empty', =>
		expect =>
			@FolderBusinessLayer.create('   ')
		.toThrow(new Error('Folder name must not be empty'))


	it 'should create a folder before theres a response from the server', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@FolderBusinessLayer.create('johns')
		expect(@FolderModel.size()).toBe(1)
		expect(@FolderModel.getByName('johns').opened).toBe(true)


	it 'should make a create folder request', =>
		@persistence.createFolder = jasmine.createSpy('add folder')
		
		@FolderBusinessLayer.create(' johns ')
		expect(@persistence.createFolder).toHaveBeenCalledWith('johns', 0,
			jasmine.any(Function))


	it 'should call the onSuccess function on response status ok', =>
		onSuccess = jasmine.createSpy('Success')
		@persistence.createFolder = jasmine.createSpy('add folder')
		@persistence.createFolder.andCallFake (folderName, parentId, success) =>
			@response =
				status: 'ok'
				data: 'jooo'
			success(@response)

		@FolderBusinessLayer.create(' johns ', onSuccess)

		expect(onSuccess).toHaveBeenCalledWith(@response.data)


	it 'should call the handle a response error when creating a folder', =>
		onSuccess = jasmine.createSpy('Success')
		onFailure = jasmine.createSpy('Failure')
		@persistence.createFolder = jasmine.createSpy('add folder')
		@persistence.createFolder.andCallFake (folderName, parentId, success) =>
			@response =
				status: 'error'
				msg: 'this is an error'
			success(@response)

		@FolderBusinessLayer.create(' johns ', onSuccess, onFailure)

		expect(onSuccess).not.toHaveBeenCalled()
		expect(onFailure).toHaveBeenCalled()

		expect(@FolderModel.getByName('johns').error).toBe(@response.msg)


	it 'should mark a folder error as read by removing it', =>
		@FolderModel.add({id: 3, name: 'john'})

		@FolderBusinessLayer.markErrorRead('John')

		expect(@FolderModel.size()).toBe(0)
		expect(@FolderModel.getByName('john')).toBe(undefined)


	it 'should return the corret folder for id', =>
		item = {id: 3, name: 'john'}
		@FolderModel.add(item)

		expect(@FolderBusinessLayer.getById(3)).toBe(item)


	it 'should open a folder', =>
		@persistence.openFolder = jasmine.createSpy('open')

		@FolderModel.add({id: 3, opened: false, name: 'ho'})
		@FolderBusinessLayer.open(3)
		expect(@FolderModel.getById(3).opened).toBeTruthy()
		expect(@persistence.openFolder).toHaveBeenCalledWith(3)


	it 'should not import on empty opml', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFeed = jasmine.createSpy('create feed')

		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>

			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).not.toHaveBeenCalled()
		expect(@persistence.createFeed).not.toHaveBeenCalled()


	it 'should import a folder', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFeed = jasmine.createSpy('create feed')

		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>
			  		<outline text="Design" title="Design" />
			  		<outline text="test" title="test"></outline>
			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).toHaveBeenCalledWith('test', 0,
			jasmine.any(Function))
		expect(@persistence.createFeed).not.toHaveBeenCalled()


	it 'should import a feed', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFeed = jasmine.createSpy('create feed')

		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>
			  		<outline htmlUrl="http://worrydream.com/" text=
			      "&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      title="&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      type="rss"
			      version="RSS" xmlUrl="http://worrydream.com/feed.xml"/>
			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).not.toHaveBeenCalled()
		expect(@persistence.createFeed).toHaveBeenCalledWith(
			'http://worrydream.com/feed.xml', 0, jasmine.any(Function))


	it 'should import nested folders', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFolder.andCallFake (name, parentId, onSuccess) =>
			data =
				data:
					folders: [
						{id: 3}
					]
			onSuccess(data)

		@persistence.createFeed = jasmine.createSpy('create feed')

		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>
			  	<outline text="Design" title="Design">
			  		<outline htmlUrl="http://worrydream.com/" text=
			      "&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      title="&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      type="rss"
			      version="RSS" xmlUrl="http://worrydream.com/feed.xml"/>
			    </outline>
			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).toHaveBeenCalledWith('Design', 0,
			jasmine.any(Function))
		expect(@persistence.createFeed).toHaveBeenCalledWith(
			'http://worrydream.com/feed.xml', 3, jasmine.any(Function))


	it 'should use an existing folder when importing a folder', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFeed = jasmine.createSpy('create feed')
		@persistence.openFolder = jasmine.createSpy('open')

		folder = {id: 2, name: 'design', opened: false}
		@FolderModel.add(folder)
		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>
			  	<outline text="Design" title="Design">
			  		<outline htmlUrl="http://worrydream.com/" text=
			      "&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      title="&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      type="rss"
			      version="RSS" xmlUrl="http://worrydream.com/feed.xml"/>
			    </outline>
			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).not.toHaveBeenCalled()
		expect(@persistence.createFeed).toHaveBeenCalledWith(
			'http://worrydream.com/feed.xml', 2, jasmine.any(Function))
		expect(folder.opened).toBe(true)
		expect(@persistence.openFolder).toHaveBeenCalled()



	it 'should not import a feed if it already exists', =>
		@persistence.createFolder = jasmine.createSpy('create folder')
		@persistence.createFeed = jasmine.createSpy('create feed')

		@FeedModel.add({url: 'http://worrydream.com/feed.xml'})

		xml = '<?xml version="1.0" ?>
			<opml version="1.1">
			  <!--Generated by NewsBlur - www.newsblur.com-->
			  <head>
			    <title>
			      NewsBlur Feeds
			    </title>
			    <dateCreated>
			      2013-03-14 16:44:01.356965
			    </dateCreated>
			    <dateModified>
			      2013-03-14 16:44:01.356965
			    </dateModified>
			  </head>
			  <body>
			  		<outline htmlUrl="http://worrydream.com/" text=
			      "&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      title="&lt;div&gt;Bret Victor\'s website&lt;/div&gt;"
			      type="rss"
			      version="RSS" xmlUrl="http://worrydream.com/feed.xml"/>
			  </body>
			</opml>'

		@FolderBusinessLayer.import(xml)

		expect(@persistence.createFolder).not.toHaveBeenCalled()
		expect(@persistence.createFeed).not.toHaveBeenCalled()