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

describe 'Loading', ->

	beforeEach module 'News'

	beforeEach inject (_Loading) =>
		@loading = new _Loading()


	it 'should have an initial value of 0', =>
		expect(@loading.getCount()).toBe(0)


	it 'should increase count when increase is called', =>
		@loading.increase()
		expect(@loading.getCount()).toBe(1)


	it 'should decrease count when decrease is called', =>
		@loading.increase()
		@loading.increase()
		@loading.increase()
		@loading.decrease()
		expect(@loading.getCount()).toBe(2)


	it 'should return false when no loading is happening', =>
		expect(@loading.isLoading()).toBe(false)


	it 'should return true when loading is happening', =>
		@loading.increase()
		expect(@loading.isLoading()).toBe(true)