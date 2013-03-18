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


describe '_Persistence', ->


	beforeEach module 'News'

	beforeEach inject (@_Persistence, @$rootScope) =>
		@req =
			post: jasmine.createSpy('POST')
			get: jasmine.createSpy('GET').andCallFake (url, p1, p2, callback) ->
				if callback
					callback()
		@config =
			itemBatchSize: 12
		@active =
			getType: -> 3
			getId: -> 1
		@loading =
			increase: ->
			decrease: ->


	it 'should should show a loading sign when init', =>
		loading =
			increase: jasmine.createSpy('loading')
			decrease: jasmine.createSpy('finished loading')
		
		pers = new @_Persistence(@req, loading, @config, @active, @$rootScope)
		pers.init()

		expect(loading.increase).toHaveBeenCalled()
		expect(loading.decrease).toHaveBeenCalled()


	###
		FEED CONTROLLER
	###
	it 'should get all feeds', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getAllFeeds()

		expect(@req.get).toHaveBeenCalledWith('news_feeds', {}, {}, angular.noop)

	it 'should get a feed by id', =>
		url =
			feedId: 1

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getFeedById(url.feedId)

		expect(@req.get).toHaveBeenCalledWith('news_feed', url)


	it 'create a correct request for moving a feed', =>
		data =
			folderId: 4
		url =
			feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.moveFeed(url.feedId, data.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_move_feed', url, data)


	it 'shoud send a correct request for marking all items read', =>
		data =
			highestItemId: 4
		url =
			feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.setFeedRead(url.feedId, data.highestItemId)


		expect(@req.post).toHaveBeenCalledWith('news_set_feed_read', url, data)


	it 'send a correct feed update request', =>
		url =
			feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.updateFeed(url.feedId)

		expect(@req.post).toHaveBeenCalledWith('news_update_feed', url)


	it 'send a correct get active feed request', =>
		succs = angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getActiveFeed(succs)

		expect(@req.get).toHaveBeenCalledWith('news_active_feed', {}, {}, succs)


	it 'send a correct feed delete request', =>
		url =
			feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.deleteFeed(url.feedId)

		expect(@req.post).toHaveBeenCalledWith('news_delete_feed', url)


	it 'send a correct feed create request', =>
		data =
			parentFolderId: 5
			url: 'http://google.de'

		onsuccess = angular.noop
		onerror = angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.createFeed(data.url, data.parentFolderId, onsuccess, onerror)

		expect(@req.post).toHaveBeenCalledWith('news_create_feed', {}, data,
			onsuccess, onerror)



	###
		FOLDER CONTROLLER
	###
	it 'should do a proper get all folders request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getAllFolders()

		expect(@req.get).toHaveBeenCalledWith('news_folders', {}, {}, angular.noop)


	it 'should get a folder by id', =>
		url =
			folderId: 5

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getFolderById(url.folderId)

		expect(@req.get).toHaveBeenCalledWith('news_folder', url)


	it 'send a correct collapse folder request', =>
		url =
			folderId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.collapseFolder(url.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_collapse_folder', url)


	it 'send a correct open folder request', =>
		url =
			folderId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.openFolder(url.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_open_folder', url)


	it 'should do a proper folder create request', =>
		data =
			folderName: 'check'
			parentFolderId: 4

		onsuccess = -> 1
		onerror = -> 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.createFolder(data.folderName, data.parentFolderId, onsuccess, onerror)

		expect(@req.post).toHaveBeenCalledWith('news_create_folder', {}, data,
			onsuccess, onerror)


	it 'should do a proper folder delete request', =>
		url =
			folderId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.deleteFolder(url.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_delete_folder', url)


	it 'should do a proper folder rename request', =>
		url =
			folderId: 2
		data =
			folderName: 'host'

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.renameFolder(url.folderId, data.folderName)

		expect(@req.post).toHaveBeenCalledWith('news_rename_folder', url, data)


	###
		ITEM CONTROLLER
	###
	it 'should send a autopaging request', =>
		data =
			type: 2
			id: 5
			limit: @config.itemBatchSize
			offset: 3

		success = angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getItems(data.type, data.id, data.offset, success, null)

		expect(@req.get).toHaveBeenCalledWith('news_items', {}, data, success)


	it 'should send a load newest items request', =>
		data =
			type: 2
			id: 5
			updatedSince: 1333

		success = angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getItems(data.type, data.id, 0, success, data.updatedSince)

		expect(@req.get).toHaveBeenCalledWith('news_items', {}, data, success)


	it 'send a correct get item by id request', =>
		url =
			itemId: 5

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getItemById(url.itemId)

		expect(@req.get).toHaveBeenCalledWith('news_item', url)



	it 'send a correct get starred items request', =>
		success = angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getStarredItems(success)

		expect(@req.get).toHaveBeenCalledWith('news_starred_items', {}, {},
			success)


	it 'send a correct star item request', =>
		url =
			itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.starItem(url.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_star_item', url)


	it 'send a correct unstar item request', =>
		url =
			itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.unstarItem(url.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_unstar_item', url)


	it 'send a correct read item request', =>
		url =
			itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.readItem(url.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_read_item', url)


	it 'send a correct unread item request', =>
		url =
			itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.unreadItem(url.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_unread_item', url)


	###
		EXPORT CONTROLLER
	###
	it 'should have an export request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.exportOPML()

		expect(@req.get).toHaveBeenCalledWith('news_export_opml')


	###
		USERSETTINGS CONTROLLER
	###
	it 'should do a proper get user settings read request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsRead()

		expect(@req.get).toHaveBeenCalledWith('news_user_settings_read', {}, {},
		angular.noop)

	
	it 'should do a proper get user settings read req and call callback', =>
		callback = ->
			1 + 1
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsRead(callback)

		expect(@req.get).toHaveBeenCalledWith('news_user_settings_read', {}, {},
				callback)


	it 'should do a proper user settings read show request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsReadShow()

		expect(@req.post).toHaveBeenCalledWith('news_user_settings_read_show')



	it 'should do a proper user settings read hide request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsReadHide()

		expect(@req.post).toHaveBeenCalledWith('news_user_settings_read_hide')