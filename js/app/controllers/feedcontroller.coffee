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


angular.module('News').factory '_FeedController',
['_ExistsError',
(_ExistsError)->

	class FeedController

		constructor: (@_$scope, @_persistence, @_folderBl, @_feedBl,
		              @_subscriptionsBl, @_starredBl, @_unreadCountFormatter) ->

			@_isAddingFolder = false
			@_isAddingFeed = false

			# bind internal stuff to scope
			@_$scope.folderBl = @_folderBl
			@_$scope.feedBl = @_feedBl
			@_$scope.subscriptionsBl = @_subscriptionsBl
			@_$scope.starredBl = @_starredBl
			@_$scope.unreadCountFormatter = @_unreadCountFormatter

			
			@_$scope.isAddingFolder = =>
				return @_isAddingFolder

			@_$scope.isAddingFeed = =>
				return @_isAddingFeed

			@_$scope.addFeed = (feedUrl, parentFolderId=0) =>
				@_$scope.feedEmptyError = false
				@_$scope.feedExistsError = false
				
				try
					@_isAddingFeed = true
					@_feedBl.create feedUrl, parentFolderId
					# on success
					, (data) =>
						@_$scope.feedUrl = ''
						@_isAddingFeed = false
						@_feedBl.load(data['feeds'][0].id)
					# on error
					, =>
						@_isAddingFeed = false
				
				catch error
					if error instanceof _ExistsError
						@_$scope.feedExistsError = true
					else
						@_$scope.feedEmptyError = true
					@_isAddingFeed = false
					

			@_$scope.addFolder = (folderName) =>
				@_$scope.folderEmptyError = false
				@_$scope.folderExistsError = false

				try
					@_isAddingFolder = true
					@_folderBl.create folderName

					# on success
					, =>
						@_$scope.folderName = ''
						@_$scope.addNewFolder = false
						@_isAddingFolder = false
					# on error
					, =>
						@_isAddingFolder = false

				catch error
					if error instanceof _ExistsError
						@_$scope.folderExistsError = true
					else
						@_$scope.folderEmptyError = true
					@_isAddingFolder = false


			@_$scope.$on 'moveFeedToFolder', (scope, data) =>
				@_feedBl.move(data.feedId, data.folderId)

	return FeedController

]