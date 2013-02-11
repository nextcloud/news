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

		constructor: (@$scope, @$rootScope, @persistence, @opmlParser) ->
			
			@add = false
			@settings = false
			@addingFeed = false
			@addingFolder = false

			@$scope.$on 'readFile', (scope, fileContent) =>
				structure = @opmlParser.parseXML(fileContent)
				@parseOPMLStructure(structure)

			@$scope.$on 'hidesettings', =>
				@$scope.showSettings = false


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