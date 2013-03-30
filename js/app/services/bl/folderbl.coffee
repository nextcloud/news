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


angular.module('News').factory '_FolderBl', ->

	class FolderBl

		constructor: (@_folderModel, @_feedBl, @_persistence) ->


		delete: (folderId) ->
			@_folderModel.removeById(folderId)
			@_persistence.deleteFolder(folderId)


		hasFeeds: (folderId) ->
			return @_feedBl.getFeedsOfFolder(folderId).length


		markFolderRead: (folderId) ->
			for feed in @_feedBl.getFeedsOfFolder(folderId)
				@_feedBl.markFeedRead(feed.id)


		toggleFolder: (folderId) ->
			folder = @_folderModel.getById(folderId)
			
			if angular.isDefined(folder)
				folder.open = !folder.open
				if folder.open
					@_persistence.openFolder(folder.id)
				else
					@_persistence.collapseFolder(folder.id)


	return FolderBl
