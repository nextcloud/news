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

describe '_Model', ->

	beforeEach module 'News'

	beforeEach inject (_Model) =>
		@model = new _Model()

		@data1 =
			id: 1
			name: 'john'
			mail: 'john.com'

		@data2 =
			id: 5
			name: 'frank'
			mail: 'frank.de'

		@model.add(@data1)
		@model.add(@data2)


	it 'should return correct size', =>
		expect(@model.size()).toBe(2)


	it 'should add data', =>
		data3 =
			id: 4
			name: 'tom'
			mail: 'tom.ch'

		@model.add(data3)

		expect(@model.getById(4)).toBe(data3)


	it 'should increase size when adding data', =>
		data3 =
			id: 4

		@model.add(data3)

		expect(@model.size()).toBe(3)


	it 'should update the data if add is called with an existing id', =>
		data3 =
			id: 1
			name: 'tom'
			mail: 'tom.ch'

		@model.add(data3)

		data1 = @model.getById(1)

		expect(data1.name).toBe('tom')
		expect(data1.mail).toBe('tom.ch')
		expect(@model.size()).toBe(2)


	it 'should decrease size when en entry is deleted', =>
		@model.removeById(1)

		expect(@model.size()).toBe(1)


	it 'should remove element when entry is removed', =>
		@model.removeById(2)

		expect(@model.getById(2)).toBe(undefined)


	it 'should remove element when entry is removed', =>
		removed = @model.removeById(1)

		expect(@model.getById(1)).toBe(undefined)
		expect(removed.name).toBe('john')


	it 'should call add when handle is called', =>
		data = [{ id: 10, name: 'bruce'}]

		@model.handle(data)

		expect(@model.getById(10)).toBe(data[0])


	it 'should return all entries on getAll', =>

		expect(@model.getAll()).toContain(@data1)
		expect(@model.getAll()).toContain(@data2)
		expect(@model.getAll().length).toBe(2)


	it 'should clear the data on clear', =>
		@model.clear()

		expect(@model.size()).toBe(0)
		expect(@model.getById(1)).toBe(undefined)
		expect(@model.getById(2)).toBe(undefined)


	it 'should support filters', =>
		filterMock =
			exec: jasmine.createSpy()
			hashCode: ->
				return 'a'

		@model.get(filterMock)

		expect(filterMock.exec).toHaveBeenCalledWith(@model.getAll())


	it 'should cache filters', =>
		filterMock1 =
			exec: jasmine.createSpy('1').andReturn('value')
			hashCode: ->
				return 'a'

		filterMock2 =
			exec: jasmine.createSpy('2').andReturn('value')
			hashCode: ->
				return 'a'

		@model.get(filterMock1)
		@model.get(filterMock2)

		expect(filterMock1.exec).toHaveBeenCalledWith(@model.getAll())
		expect(filterMock2.exec).not.toHaveBeenCalled()


	@testClearCache = (callback) =>
		filterMock1 =
			exec: jasmine.createSpy().andReturn('value')
			hashCode: ->
				return 'a'

		filterMock2 =
			exec: jasmine.createSpy().andReturn('value')
			hashCode: ->
				return 'a'

		@model.get(filterMock1)
		callback()
		@model.get(filterMock2)

		return [
			filterMock1
			filterMock2
		]


	it 'should clear the cache on add', =>
		mocks = @testClearCache =>
			@model.add({id: 4})
		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).toHaveBeenCalled()


	it 'should clear the cache on update', =>
		mocks = @testClearCache =>
			@model.update({id: 1})

		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).toHaveBeenCalled()


	it 'should clear the cache on add', =>
		mocks = @testClearCache =>
			@model.removeById(1)

		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).toHaveBeenCalled()


	it 'should not clear the cache on remove when false passed as 2nd param', =>
		mocks = @testClearCache =>
			@model.removeById(1, false)

		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).not.toHaveBeenCalled()


	it 'should not clear the cache on update when false passed as 2nd param', =>
		mocks = @testClearCache =>
			@model.update({id: 1}, false)

		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).not.toHaveBeenCalled()


	it 'should not clear the cache on add when false passed as 2nd param', =>
		mocks = @testClearCache =>
			@model.add({id: 4}, false)

		expect(mocks[0].exec).toHaveBeenCalled()
		expect(mocks[1].exec).not.toHaveBeenCalled()
