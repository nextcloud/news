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


angular.module('News').factory 'FolderBusinessLayer',
['_BusinessLayer', 'FolderModel', 'FeedBusinessLayer', 'Persistence',
'FeedType', 'ActiveFeed', 'ItemModel', 'ShowAll', '_ExistsError', 'OPMLParser',
'NewestItem', 'FeedModel', '$rootScope',
(_BusinessLayer, FolderModel, FeedBusinessLayer, Persistence, FeedType,
ActiveFeed, ItemModel, ShowAll, _ExistsError, OPMLParser, NewestItem,
FeedModel, $rootScope) ->

	class FolderBusinessLayer extends _BusinessLayer

		constructor: (@_folderModel, @_feedBusinessLayer, @_showAll, activeFeed,
			          persistence, @_feedType, itemModel, @_opmlParser,
			          @_newestItem, @_feedModel, @_$rootScope) ->
			super(activeFeed, persistence, itemModel, @_feedType.Folder)


		getById: (folderId) ->
			return @_folderModel.getById(folderId)


		delete: (folderId) ->
			feeds = []
			# also delete feeds
			for feed in @_feedBusinessLayer.getFeedsOfFolder(folderId)
				feeds.push(@_feedModel.removeById(feed.id))

			folder = @_folderModel.removeById(folderId)
		
			data =
				undoCallback: =>
					@_persistence.restoreFolder folderId, =>
						@_persistence.getAllFeeds()
						@_persistence.getAllFolders()
				caption: folder.name

			@_$rootScope.$broadcast 'undoMessage', data
			@_persistence.deleteFolder(folderId)



		hasFeeds: (folderId) ->
			return @_feedBusinessLayer.getFeedsOfFolder(folderId).length


		open: (folderId) ->
			folder = @_folderModel.getById(folderId)
			if angular.isDefined(folder)
				if not folder.opened
					folder.opened = true
					@_persistence.openFolder(folder.id)


		toggleFolder: (folderId) ->
			folder = @_folderModel.getById(folderId)
			
			if angular.isDefined(folder)
				folder.opened = !folder.opened
				if folder.opened
					@_persistence.openFolder(folder.id)
				else
					@_persistence.collapseFolder(folder.id)


		markRead: (folderId) ->
			newestItemId = @_newestItem.getId()
			folder = @_folderModel.getById(folderId)

			if newestItemId != 0 and angular.isDefined(folder)
				for feed in @_feedBusinessLayer.getFeedsOfFolder(folderId)
					feed.unreadCount = 0

					# also set items in feeds as read
					for item in @_itemModel.getAll()
						if item.feedId == feed.id
							item.setRead()

				@_persistence.setFolderRead(folderId, newestItemId)


		getUnreadCount: (folderId) ->
			return @_feedBusinessLayer.getFolderUnreadCount(folderId)
			

		isVisible: (folderId) ->
			if @_showAll.getShowAll()
				return true
			else
				if @isActive(folderId) or
				@_feedBusinessLayer.getFolderUnreadCount(folderId) > 0
					return true
				if @_activeFeed.getType() == @_feedType.Feed
					for feed in @_feedBusinessLayer.getFeedsOfFolder(folderId)
						if feed.id == @_activeFeed.getId()
							return true
				return false


		getAll: ->
			return @_folderModel.getAll()


		create: (folderName, onSuccess=null, onFailure=null) ->
			onSuccess or= ->
			onFailure or= ->

			if angular.isUndefined(folderName) or folderName.trim() == ''
				throw new Error('Folder name must not be empty')
			
			folderName = folderName.trim()
			
			if @_folderModel.getByName(folderName)
				throw new _ExistsError('Exists already')

			folder =
				name: folderName
				opened: true

			@_folderModel.add(folder)

			success = (response) =>
				if response.status == 'error'
					folder.error = response.msg
					onFailure()
				else
					onSuccess(response.data)

			@_persistence.createFolder folderName, 0, success


		markErrorRead: (folderName) ->
			@_folderModel.removeByName(folderName)


		import: (xml) ->
			opml = @_opmlParser.parseXML(xml)
			@_importElement(opml, 0)


		_importElement: (opml, parentFolderId) ->
			for item in opml.getItems()
				do (item) =>
					if item.isFolder()
						try
							@create item.getName(), (data) =>
								@_importElement(item, data.folders[0].id)
						catch error
							if error instanceof _ExistsError
								folder = @_folderModel.getByName(item.getName())
								@open(folder.id)
								@_importElement(item, folder.id)
							else
								console.info error
					else
						try
							@_feedBusinessLayer.create(item.getUrl(),
								parentFolderId)
						catch error
							if not error instanceof _ExistsError
								console.info error


	return new FolderBusinessLayer(FolderModel, FeedBusinessLayer, ShowAll,
	                               ActiveFeed, Persistence, FeedType, ItemModel,
	                               OPMLParser, NewestItem, FeedModel, $rootScope)

]