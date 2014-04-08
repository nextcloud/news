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

describe '_MinimumQuery', ->


	beforeEach module 'News'

	beforeEach inject (_MinimumQuery, _Model, _Query) =>
		@query = _MinimumQuery
		@q = _Query
		@model = _Model


	it 'should be a _Query subclass', =>
		expect(new @query('id') instanceof @q).toBe(true)


	it 'should have a correct hash', =>
		expect(new @query('id').hashCode()).toBe('minimum_id')


	it 'should return undefined on empty list', =>
		query = new @query('id')
		expect(query.exec([])).toBe(undefined)


	it 'should return the minimum', =>
		data1 =
			id: 3

		data2 =
			id: 1

		data3 =
			id: 5

		data = [
			data1
			data2
			data3
		]
		query = new @query('id')

		expect(query.exec(data)).toBe(data2)
