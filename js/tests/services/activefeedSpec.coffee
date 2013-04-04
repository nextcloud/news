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


describe 'ActiveFeed', ->

	beforeEach module 'News'

	beforeEach inject (@ActiveFeed, @FeedType) =>
		@data =
			id: 5
			type: 3


	it 'should be Subscriptions by default', =>
		expect(@ActiveFeed.getType()).toBe(@FeedType.Subscriptions)


	it 'should set the correct feed id', =>
		@ActiveFeed.handle(@data)
		expect(@ActiveFeed.getId()).toBe(5)


	it 'should set the correct feed type', =>
		@ActiveFeed.handle(@data)
		expect(@ActiveFeed.getType()).toBe(3)