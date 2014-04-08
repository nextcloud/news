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

describe '_Query', ->


	beforeEach module 'News'

	beforeEach inject (_Query, _NotImplementedError) =>
		@query = _Query
		@error = _NotImplementedError


	it 'should create a basic hash', =>
		name = 'message'
		args = [
			'a',
			1,
			1.3,
			true
		]
		query = new @query(name, args)

		expect(query.hashCode()).toBe('message_a_1_1.3_true')


	it 'should escape underlines of field names to avoid collissions', =>
		query = new @query('message', ['test__a'])
		expect(query.hashCode()).toBe('message_test____a')


	it 'should throw an error when filtering', =>
		expect =>
			new @query().exec()
		.toThrow()


	it 'should have the same hash for two identical objects', =>
		name = 'message'
		args = [
			'a',
			1,
			1.3,
			true
		]
		filter1 = new @query(name, args)
		filter2 = new @query(name, args)

		expect(filter1.hashCode()).toBe(filter2.hashCode())