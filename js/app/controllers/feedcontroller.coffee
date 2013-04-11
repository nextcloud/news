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


angular.module('News').factory '_FeedController', ->

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
				@_$scope.feedError = false
				
				if angular.isUndefined(feedUrl) or feedUrl.trim() == ''
					@_$scope.feedEmptyError = true
				
				if not @_$scope.feedEmptyError
					@_isAddingFeed = true

					onError = =>
						@_$scope.feedError = true
						@_isAddingFeed = false

					onSuccess = (data) =>
						if data.status == 'error'
							onError()
						else
							@_$scope.feedUrl = ''
							@_isAddingFeed = false
					
					@_persistence.createFeed(feedUrl.trim(), parentFolderId,
						onSuccess, onError)
				

			@_$scope.addFolder = (folderName) =>
				@_$scope.folderEmptyError = false
				@_$scope.folderExistsError = false

				if angular.isUndefined(folderName) or folderName.trim() == ''
					@_$scope.folderEmptyError = true
				else
					folderName = folderName.trim()
					if @_folderModel.nameExists(folderName)
						@_$scope.folderExistsError = true

				if not (@_$scope.folderEmptyError or @_$scope.folderExistsError)
					@_isAddingFolder = true
					@_persistence.createFolder folderName, 0, =>
						@$scope.folderName = ''
						@$scope.addNewFolder = false
						@_isAddingFolder = false


			@_$scope.$on 'moveFeedToFolder', (scope, data) =>
				console.log data

	return FeedController