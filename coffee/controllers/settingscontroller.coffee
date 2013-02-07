###
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
###

angular.module('News').factory '_SettingsController', ['Controller', 
(Controller) ->

	class SettingsController extends Controller

		constructor: (@$scope, @$rootScope, @showAll, @persistence, @folderModel, 
						@feedModel, @opmlParser) ->
			
			@add = false
			@settings = false
			@addingFeed = false
			@addingFolder = false

			@$scope.getFolders = =>
				return @folderModel.getItems()

			@$scope.getShowAll = =>
				return @showAll.showAll

			@$scope.setShowAll = (value) =>
				@showAll.showAll = value
				@persistence.showAll(value)
				@$rootScope.$broadcast('triggerHideRead')

			@$scope.toggleSettings = =>
				@settings = !@settings

			@$scope.toggleAdd = =>
				@add = !@add

			@$scope.addIsShown = =>
				return @add

			@$scope.settingsAreShown = =>
				return @settings

			@$scope.isAddingFeed = =>
				return @addingFeed

			@$scope.isAddingFolder = =>
				return @addingFolder

			@$scope.addFeed = (url, folder) =>
				@$scope.feedEmptyError = false
				@$scope.feedExistsError = false
				@$scope.feedError = false
			
				if url == undefined or url.trim() == ''
					@$scope.feedEmptyError = true
				else 
					url = url.trim()
					for feed in @feedModel.getItems()
						if url == feed.url # FIXME: can we really compare this
							@$scope.feedExistsError = true
				
				if not (@$scope.feedEmptyError or @$scope.feedExistsError)
					if folder == undefined
						folderId = 0
					else
						folderId = folder.id
					@addingFeed = true
					onSuccess = =>
						@$scope.feedUrl = ''
						@addingFeed = false
					onError = =>
						@$scope.feedError = true
						@addingFeed = false
					@persistence.createFeed(url, folderId, onSuccess, onError)


			@$scope.addFolder = (name) =>
				@$scope.folderEmptyError = false
				@$scope.folderExistsError = false
			
				if name == undefined or name.trim() == ''
					@$scope.folderEmptyError = true
				else 
					name = name.trim()
					for folder in @folderModel.getItems()
						if name.toLowerCase() == folder.name.toLowerCase()
							@$scope.folderExistsError = true
				
				if not (@$scope.folderEmptyError or @$scope.folderExistsError)
					@addingFolder = true
					onSuccess = =>
						@$scope.folderName = ''
						@addingFolder = false
					@persistence.createFolder(name, onSuccess)
				
			@$scope.$on 'readFile', (scope, fileContent) =>
				structure = @opmlParser.parseXML(fileContent)
				@parseOPMLStructure(structure)
				

			@$scope.$on 'hidesettings', =>
				@add = false
				@settings = false


		# recursively create folders
		parseOPMLStructure: (structure, folderId=0) ->
			for item in structure.getItems()
				if item.isFolder()
					onSuccess = (data) =>
						console.log data
						folderId = data.folders[0].id
						@parseOPMLStructure(item, folderId)
					@persistence.createFolder(item.getName(), onSuccess)
				else
					# FIXME: handle errors
					onSuccess = ->
					onError = ->
					@persistence.createFeed(item.getUrl(), folderId, onSuccess, onError)


	return SettingsController
]