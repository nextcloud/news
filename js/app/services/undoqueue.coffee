###

ownCloud - App Framework

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

# A class which follows the command pattern
# Can be used for actions that need need to be able to undo like folder deletion
angular.module('News').factory 'UndoQueue',
['$timeout', '$rootScope',
($timeout, $rootScope) ->

	class UndoQueue

		constructor: (@_$timeout, @_$rootScope) ->
			@_queue = []


		add: (@_caption, @_callback, @_timeout=0, @_undoCallback=null) ->
			###
			@_caption the caption which indentifies the item
			@_callback function the callback which should be executed when it was
			not undone, this will usually be a request to the server to finally
			delete something
			@_timeout int the timeout after the callback should be executed
			defaults to 0
			@_undoCallback function the function which should be executed when
			an command has been canceled. Usually this will add back a deleted
			object back to the interface, defaults to an empty function
			###
			@_executeAll()

			command =
				_undoCallback: @_undoCallback or= ->
				_callback: @_callback
				execute: =>
					command._callback()
				undo: =>
					command._undoCallback()
					@_$timeout.cancel(command.promise)
					@_queue = []
				promise: @_$timeout =>
					command.execute()
					@_$rootScope.$broadcast('notUndone')
				, @_timeout

			data =
				undoCallback: command.undo
				caption: @_caption

			@_$rootScope.$broadcast 'undoMessage', data

			@_queue.push(command)


		_executeAll: ->
			###
			Executes the callback before the timeout has run out
			This is useful to execute all remaining commands if a new command is
			added
			###
			for command in @_queue
				@_$timeout.cancel(command.promise)
				command.execute()
			@_queue = []


	return new UndoQueue($timeout, $rootScope)

]