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

describe 'FolderModel', ->


	beforeEach module 'News'

	beforeEach inject (@FolderModel, @_Model, @_EqualQuery) =>


	it 'should extend model', =>
		expect(@FolderModel instanceof @_Model).toBeTruthy()


	it 'should add folders without id but name if they dont exist yet', =>
		item = {name: 'Hi'}
		@FolderModel.add(item)
		item1 = {name: 'His'}
		@FolderModel.add(item1)
		expect(@FolderModel.getByName('hi')).toBe(item)
		expect(@FolderModel.size()).toBe(2)


	it 'should clear the fodername cache', =>
		item = {name: 'Hi'}
		@FolderModel.add(item)
		@FolderModel.clear()
		expect(@FolderModel.getByName('hi')).toBe(undefined)
		expect(@FolderModel.size()).toBe(0)


	it 'should delete items from the fodername cache', =>
		item = {id: 3, name: 'Hi'}
		@FolderModel.add(item)
		@FolderModel.removeById(3)
		expect(@FolderModel.getByName('hi')).toBe(undefined)
		expect(@FolderModel.size()).toBe(0)


	it 'should update by foldername', =>
		item = {name: 'Hi'}
		@FolderModel.add(item)

		item2 = {name: 'hi', test: 'hoho'}
		@FolderModel.add(item2)

		expect(@FolderModel.getByName('hi').test).toBe('hoho')
		expect(@FolderModel.size()).toBe(1)


	it 'should update the id if an update comes in with an id', =>
		item = {name: 'Tony'}
		@FolderModel.add(item)

		item2 = {id: 3, name: 'tony', test: 'hoho'}
		@FolderModel.add(item2)

		expect(@FolderModel.getByName('Tony').id).toBe(3)
		expect(@FolderModel.getByName('Tony').test).toBe('hoho')
		expect(@FolderModel.getById(3).id).toBe(3)
		expect(@FolderModel.getById(3).test).toBe('hoho')
		expect(@FolderModel.size()).toBe(1)


	it 'should update normally', =>
		item = {id: 3, name: 'His'}
		@FolderModel.add(item)

		item2 = {id: 3, name: 'hobo', test: 'hoho'}
		@FolderModel.add(item2)

		expect(@FolderModel.getByName('His')).toBe(undefined)
		expect(@FolderModel.getByName('Hobo').id).toBe(3)
		expect(@FolderModel.getByName('Hobo').test).toBe('hoho')
		expect(@FolderModel.getById(3).test).toBe('hoho')
		expect(@FolderModel.size()).toBe(1)



	it 'should clear invalidate the query cache on adding folder with name', =>
		item = {name: 'name1', test: 'hi'}
		query = new @_EqualQuery('test', 'hi')
		
		expect(@FolderModel.get(query).length).toBe(0)
		@FolderModel.add(item, false)

		expect(@FolderModel.get(query).length).toBe(0)

		item2 = {name: 'name',  test: 'hi'}
		@FolderModel.add(item2)

		expect(@FolderModel.get(query).length).toBe(2)