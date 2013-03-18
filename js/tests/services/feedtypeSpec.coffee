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


describe 'FeedType', ->


	beforeEach module 'News'

	beforeEach inject (@FeedType) =>


	it 'should have the correct folder number', =>
		expect(@FeedType.Feed).toBe(0)


	it 'should have the correct folder number', =>
		expect(@FeedType.Folder).toBe(1)


	it 'should have the correct folder number', =>
		expect(@FeedType.Starred).toBe(2)


	it 'should have the correct folder number', =>
		expect(@FeedType.Subscriptions).toBe(3)


	it 'should have the correct folder number', =>
		expect(@FeedType.Shared).toBe(4 )
