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


describe 'Persistence', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@req =
			get: jasmine.createSpy('get')
			delete: jasmine.createSpy('delete')
			post: jasmine.createSpy('post')
		@config =
			itemBatchSize: 3

		@feedLoading =
			increase: jasmine.createSpy('feedLoading increase')
			decrease: jasmine.createSpy('feedLoading decrease')

		$provide.value 'Request', @req
		$provide.value 'Config', @config
		$provide.value 'FeedLoading', @feedLoading
		return


	beforeEach inject (@Persistence) =>
		


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
			onSuccess: ->

		@Persistence.getItems(params.data.type, params.data.id,
		                      params.data.offset, params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)
			data:
				type: 2
				id: 5
				limit: @config.itemBatchSize
				offset: 3

		expect(@req.get).toHaveBeenCalledWith('news_items', expected)


	it 'should send a load new items request', =>
		success = ->
		params =
			data:
				type: 2
				id: 5
				lastModified: 3
			onSuccess: success
			onFailure: success

		@Persistence.getNewItems(params.data.type, params.data.id,
		                      params.data.lastModified, success)

		expect(@req.get).toHaveBeenCalledWith('news_items_new', params)


	it 'send a correct star item request', =>
		params =
			routeParams:
				feedId: 2
				guidHash: 'dfdfdf'

		@Persistence.starItem(params.routeParams.feedId, params.routeParams.guidHash)

		expect(@req.post).toHaveBeenCalledWith('news_items_star', params)


	it 'send a correct unstar item request', =>
		params =
			routeParams:
				feedId: 2
				guidHash: 'dfdfdf'

		@Persistence.unstarItem(params.routeParams.feedId,
			params.routeParams.guidHash)

		expect(@req.post).toHaveBeenCalledWith('news_items_unstar', params)


	it 'send a correct read item request', =>
		params =
			routeParams:
				itemId: 2


		@Persistence.readItem(params.routeParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_items_read', params)


	it 'send a correct unread item request', =>
		params =
			routeParams:
				itemId: 2

		@Persistence.unreadItem(params.routeParams.itemId)

		expect(@req.post).toHaveBeenCalledWith('news_items_unread', params)


	it 'shoud send a correct request for marking all items read', =>
		params =
			data:
				highestItemId: 4

		@Persistence.setAllRead(params.data.highestItemId)



	###
		FEED CONTROLLER
	###
	it 'should get all feeds', =>

		params =
			onSuccess: ->

		@Persistence.getAllFeeds(params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)

		expect(@req.get).toHaveBeenCalledWith('news_feeds', expected)


	it 'should not show loading sign if disabled', =>
		success = ->
		@Persistence.getAllFeeds(success, false)
		expect(@feedLoading.increase).not.toHaveBeenCalled()


	it 'create a correct request for moving a feed', =>
		params =
			data:
				parentFolderId: 4
			routeParams:
				feedId: 3

		@Persistence.moveFeed(params.routeParams.feedId, params.data.parentFolderId)

		expect(@req.post).toHaveBeenCalledWith('news_feeds_move', params)


	it 'create a correct request for renaming a feed', =>
		params =
			data:
				feedTitle: "New Feed Title"
			routeParams:
				feedId: 3

		@Persistence.renameFeed(params.routeParams.feedId, params.data.feedTitle)

		expect(@req.post).toHaveBeenCalledWith('news_feeds_rename', params)


	it 'shoud send a correct request for marking all items of a feed read', =>
		params =
			data:
				highestItemId: 4
			routeParams:
				feedId: 3

		@Persistence.setFeedRead(params.routeParams.feedId, params.data.highestItemId)


		expect(@req.post).toHaveBeenCalledWith('news_feeds_read', params)


	it 'send a correct feed update request', =>
		params =
			routeParams:
				feedId: 3

		@Persistence.updateFeed(params.routeParams.feedId)

		expect(@req.post).toHaveBeenCalledWith('news_feeds_update', params)


	it 'send a correct get active feed request', =>
		params =
			onSuccess: ->

		@Persistence.getActiveFeed(params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)

		expect(@req.get).toHaveBeenCalledWith('news_feeds_active', expected)


	it 'send a correct feed delete request', =>
		params =
			routeParams:
				feedId: 3

		@Persistence.deleteFeed(params.routeParams.feedId)

		expect(@req.delete).toHaveBeenCalledWith('news_feeds_delete', params)


	it 'send a correct feed restore request', =>
		params =
			onSuccess: ->
			routeParams:
				feedId: 3

		@Persistence.restoreFeed(params.routeParams.feedId, params.onSuccess)

		expect(@req.post).toHaveBeenCalledWith('news_feeds_restore', params)


	it 'send a correct feed create request', =>
		params =
			data:
				parentFolderId: 5
				url: 'http://google.de'
			onSuccess: ->
			onFailure: ->

		@Persistence.createFeed(params.data.url, params.data.parentFolderId,
						params.onSuccess, params.onFailure)

		expect(@req.post).toHaveBeenCalledWith('news_feeds_create', params)


	it 'should do a proper import articles request', =>
		params =
			data:
				json: {"some": "json"}
			onSuccess: jasmine.any(Function)

		@Persistence.importArticles(params.data.json, ->)


		expect(@req.post).toHaveBeenCalledWith('news_feeds_import_articles',
			params)


	###
		FOLDER CONTROLLER
	###
	it 'should do a proper get all folders request', =>
		params =
			onSuccess: ->

		@Persistence.getAllFolders(params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)

		expect(@req.get).toHaveBeenCalledWith('news_folders', expected)


	it 'send a correct collapse folder request', =>
		params =
			routeParams:
				folderId: 3

		@Persistence.collapseFolder(params.routeParams.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_folders_collapse', params)


	it 'send a correct open folder request', =>
		params =
			routeParams:
				folderId: 3

		@Persistence.openFolder(params.routeParams.folderId)

		expect(@req.post).toHaveBeenCalledWith('news_folders_open', params)


	it 'should do a proper folder create request', =>
		params =
			data:
				folderName: 'check'
				parentFolderId: 4
			onSuccess: -> 1
			onFailure: -> 2

		@Persistence.createFolder(params.data.folderName, params.data.parentFolderId,
			params.onSuccess, params.onFailure)

		expect(@req.post).toHaveBeenCalledWith('news_folders_create', params)


	it 'should do a proper folder delete request', =>
		params =
			routeParams:
				folderId: 2

		@Persistence.deleteFolder(params.routeParams.folderId)

		expect(@req.delete).toHaveBeenCalledWith('news_folders_delete', params)


	it 'send a correct folder restore request', =>
		params =
			onSuccess: ->
			routeParams:
				folderId: 3

		@Persistence.restoreFolder(params.routeParams.folderId, params.onSuccess)

		expect(@req.post).toHaveBeenCalledWith('news_folders_restore', params)


	it 'should do a proper folder rename request', =>
		params =
			routeParams:
				folderId: 2
			data:
				folderName: 'host'

		@Persistence.renameFolder(params.routeParams.folderId, params.data.folderName)

		expect(@req.post).toHaveBeenCalledWith('news_folders_rename', params)


	it 'shoud send a correct request for marking all items of a folders read', =>
		params =
			data:
				highestItemId: 4
			routeParams:
				folderId: 3

		@Persistence.setFolderRead(params.routeParams.folderId,
		                           params.data.highestItemId)


		expect(@req.post).toHaveBeenCalledWith('news_folders_read', params)


	###
		EXPORT CONTROLLER
	###
	it 'should have an export request', =>
		@Persistence.exportOPML()

		expect(@req.get).toHaveBeenCalledWith('news_export_opml')


	###
		USERSETTINGS CONTROLLER
	###
	it 'should do a proper get user settings read request', =>

		params =
			onSuccess: ->

		@Persistence.userSettingsRead(params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)

		expect(@req.get).toHaveBeenCalledWith('news_usersettings_read', expected)

	

	it 'should do a proper user settings read show request', =>
		params =
			onSuccess: ->

		@Persistence.userSettingsReadShow(params.onSuccess)

		expect(@req.post).toHaveBeenCalledWith('news_usersettings_read_show',
			params)


	it 'should do a proper user settings read hide request', =>
		params =
			onSuccess: ->
		@Persistence.userSettingsReadHide(params.onSuccess)

		expect(@req.post).toHaveBeenCalledWith('news_usersettings_read_hide',
			params)


	it 'should do a proper user settings language request', =>
		params =
			onSuccess: ->

		@Persistence.userSettingsLanguage(params.onSuccess)

		expected =
			onSuccess: jasmine.any(Function)
			onFailure: jasmine.any(Function)

		expect(@req.get).toHaveBeenCalledWith('news_usersettings_language',
			expected)


	it 'should send a get compact view request', =>
		@Persistence.userSettingsIsCompact()

		expect(@req.get).toHaveBeenCalledWith('news_usersettings_iscompact')


	it 'should send a set compact view request', =>
		@Persistence.userSettingsSetCompact(true)

		expected =
			data:
				compact: true

		expect(@req.post).toHaveBeenCalledWith('news_usersettings_setcompact',
			expected)
