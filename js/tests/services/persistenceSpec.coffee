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


	xit 'should should show a loading sign when init', =>
		loading =
			increase: jasmine.createSpy('loading')
			decrease: jasmine.createSpy('finished loading')
		
		pers = new @_Persistence(@req, loading, @config, @active, @$rootScope)
		pers.init()

		expect(loading.increase).toHaveBeenCalled()
		expect(loading.decrease).toHaveBeenCalled()


	###
		ITEM CONTROLLER
	###
	it 'should send a autopaging request', =>
		params =
			data:
				type: 2
				id: 5
				limit: @config.itemBatchSize
				offset: 3
			onSuccess: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getItems(params.data.type, params.data.id, params.data.offset,
			params.onSuccess, null)

		expect(@req.get).toHaveBeenCalledWith('news_items', params)


	it 'should send a load newest items request', =>
		params =
			data:
				type: 2
				id: 5
				updatedSince: 1333
			onSuccess: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getItems(params.data.type, params.data.id, 0, params.onSuccess,
						params.data.updatedSince)

		expect(@req.get).toHaveBeenCalledWith('news_items', params)


	it 'send a correct get starred items request', =>
		params =
			onSuccess: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getStarredItems(params.onSuccess)

		expect(@req.get).toHaveBeenCalledWith('news_starred_items', params)


	it 'send a correct star item request', =>
		params =
			urlParams:
				itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.starItem(params.urlParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_star_item', params)


	it 'send a correct unstar item request', =>
		params =
			urlParams:
				itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.unstarItem(params.urlParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_unstar_item', params)


	it 'send a correct read item request', =>
		params =
			urlParams:
				itemId: 2


		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.readItem(params.urlParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_read_item', params)


	it 'send a correct unread item request', =>
		params =
			urlParams:
				itemId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.unreadItem(params.urlParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_unread_item', params)



	###
		FEED CONTROLLER
	###
	it 'should get all feeds', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getAllFeeds()

		params =
			onSuccess: angular.noop

		expect(@req.get).toHaveBeenCalledWith('news_feeds', params)


	it 'create a correct request for moving a feed', =>
		params =
			data:
				folderId: 4
			urlParams:
				feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.moveFeed(params.urlParams.feedId, params.data.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_move_feed', params)


	it 'shoud send a correct request for marking all items read', =>
		params =
			data:
				highestItemId: 4
			urlParams:
				feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.setFeedRead(params.urlParams.feedId, params.data.highestItemId)


		expect(@req.post).toHaveBeenCalledWith('news_set_feed_read', params)


	it 'send a correct feed update request', =>
		params =
			urlParams:
				feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.updateFeed(params.urlParams.feedId)

		expect(@req.post).toHaveBeenCalledWith('news_update_feed', params)


	it 'send a correct get active feed request', =>
		params =
			onSuccess: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getActiveFeed(params.onSuccess)

		expect(@req.get).toHaveBeenCalledWith('news_active_feed', params)


	it 'send a correct feed delete request', =>
		params =
			urlParams:
				feedId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.deleteFeed(params.urlParams.feedId)

		expect(@req.post).toHaveBeenCalledWith('news_delete_feed', params)


	it 'send a correct feed create request', =>
		params =
			data:
				parentFolderId: 5
				url: 'http://google.de'
			onSuccess: angular.noop
			onFailure: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.createFeed(params.data.url, params.data.parentFolderId,
						params.onSuccess, params.onFailure)

		expect(@req.post).toHaveBeenCalledWith('news_create_feed', params)



	###
		FOLDER CONTROLLER
	###
	it 'should do a proper get all folders request', =>
		params =
			onSuccess: angular.noop

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.getAllFolders(params.onSuccess)

		expect(@req.get).toHaveBeenCalledWith('news_folders', params)


	it 'send a correct collapse folder request', =>
		params =
			urlParams:
				folderId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.collapseFolder(params.urlParams.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_collapse_folder', params)


	it 'send a correct open folder request', =>
		params =
			urlParams:
				folderId: 3

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.openFolder(params.urlParams.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_open_folder', params)


	it 'should do a proper folder create request', =>
		params =
			data:
				folderName: 'check'
				parentFolderId: 4
			onSuccess: -> 1
			onFailure: -> 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.createFolder(params.data.folderName, params.data.parentFolderId,
			params.onSuccess, params.onFailure)

		expect(@req.post).toHaveBeenCalledWith('news_create_folder', params)


	it 'should do a proper folder delete request', =>
		params =
			urlParams:
				folderId: 2

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.deleteFolder(params.urlParams.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_delete_folder', params)


	it 'should do a proper folder rename request', =>
		params =
			urlParams:
				folderId: 2
			data:
				folderName: 'host'

		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.renameFolder(params.urlParams.folderId, params.data.folderName)

		expect(@req.post).toHaveBeenCalledWith('news_rename_folder', params)


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

		params =
			onSuccess: angular.noop

		expect(@req.get).toHaveBeenCalledWith('news_user_settings_read', params)

	
	it 'should do a proper get user settings read req and call callback', =>
		params =
			onSuccess: ->
				1 + 1
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsRead(params.onSuccess)

		expect(@req.get).toHaveBeenCalledWith('news_user_settings_read', params)


	it 'should do a proper user settings read show request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsReadShow()

		expect(@req.post).toHaveBeenCalledWith('news_user_settings_read_show')



	it 'should do a proper user settings read hide request', =>
		pers = new @_Persistence(@req, @loading, @config, @active, @$rootScope)
		pers.userSettingsReadHide()

		expect(@req.post).toHaveBeenCalledWith('news_user_settings_read_hide')