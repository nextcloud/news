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

describe 'UndoQueue', ->

	beforeEach module 'News'


	beforeEach inject (@UndoQueue, @$timeout, @$rootScope) =>
		@queue = @UndoQueue
	

	it 'should execute a callback', =>
		executed = false
		callback = ->
			executed = true

		@queue.add('hi', callback, 3000)

		@$timeout.flush()

		expect(executed).toBe(true)


	it 'should execute a task when a new one is added', =>
		executed = 0
		undone = 0
		callback = ->
			executed += 1

		undoCallback = ->
			undone += 1

		@queue.add('hi', callback, 3000, undoCallback)
		@queue.add('hi', callback, 3000, undoCallback)

		expect(executed).toBe(1)
		expect(undone).toBe(0)


