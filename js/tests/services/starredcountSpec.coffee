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


describe 'StarredCount', ->


	beforeEach module 'News'

	beforeEach inject (@StarredCount) =>


	it 'should be 0 by default', =>
		expect(@StarredCount.getStarredCount()).toBe(0)


	it 'should set the correct StarredCount count', =>
		@StarredCount.handle(3)
		expect(@StarredCount.getStarredCount()).toBe(3)


	it 'should provide a setter', =>
		@StarredCount.setStarredCount(15)
		expect(@StarredCount.getStarredCount()).toBe(15)