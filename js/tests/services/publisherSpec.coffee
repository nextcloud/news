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

describe '_Publisher', ->

	beforeEach module 'News'

	beforeEach =>
		@modelMock =
			handle: jasmine.createSpy()

	beforeEach =>
		inject (_Publisher) =>
			@publisher = new _Publisher()


	it 'should publish data to subscribed model', =>
		data =
			hi: 'test'

		@publisher.subscribeObjectTo @modelMock, 'test'
		@publisher.publishDataTo data, 'test'

		expect(@modelMock.handle).toHaveBeenCalledWith(data)


	it 'should publish not to unsubscribed model', =>
		data =
			hi: 'test'

		@publisher.subscribeObjectTo @modelMock, 'test1'
		@publisher.publishDataTo data, 'test'

		expect(@modelMock.handle).not.toHaveBeenCalledWith(data)


	it 'should publish data to multiple subscribed models', =>
		data =
			hi: 'test'
		data2 =
			base: 'john'
		@modelMock2 =
			handle: jasmine.createSpy()

		@publisher.subscribeObjectTo @modelMock, 'test'
		@publisher.subscribeObjectTo @modelMock2, 'test'
		@publisher.publishDataTo data, 'test'

		expect(@modelMock.handle).toHaveBeenCalledWith(data)
		expect(@modelMock2.handle).toHaveBeenCalledWith(data)