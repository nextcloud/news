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


describe 'FeedModel', ->

	beforeEach module 'News'

	beforeEach module ($provide) =>
		@imagePath = jasmine.createSpy('imagePath')
		@utils =
			imagePath: @imagePath

		$provide.value 'Utils', @utils
		return


	beforeEach inject (@FeedModel, @_Model) =>


	it 'should extend _Model', =>
		expect(@FeedModel instanceof @_Model).toBeTruthy()


	it 'should bind an imagepath to the item if the url is empty', =>
		item =
			id: 3
			faviconLink: null
			urlHash: 'hi'
		
		@FeedModel.add(item)

		expect(@utils.imagePath).toHaveBeenCalledWith('news', 'rss.svg')


	it 'should add feeds without id', =>
		item = {faviconLink: null, urlHash: 'hi'}
		@FeedModel.add(item)

		item2 = {faviconLink: null, urlHash: 'his'}
		@FeedModel.add(item2)

		expect(@FeedModel.getByUrlHash('hi')).toBe(item)
		expect(@FeedModel.size()).toBe(2)


	it 'should clear the url hash cache', =>
		item = {faviconLink: null, urlHash: 'hi'}
		@FeedModel.add(item)
		@FeedModel.clear()
		expect(@FeedModel.getByUrlHash('hi')).toBe(undefined)
		expect(@FeedModel.size()).toBe(0)


	it 'should delete items from the fodername cache', =>
		item = {id:3, faviconLink: null, urlHash: 'hi'}
		@FeedModel.add(item)
		expect(@FeedModel.size()).toBe(1)

		@FeedModel.removeById(3)
		expect(@FeedModel.getByUrlHash('hi')).toBe(undefined)
		expect(@FeedModel.size()).toBe(0)


	it 'should update the id if an update comes in with an id', =>
		item = {faviconLink: null, urlHash: 'hi', test: 'heheh'}
		@FeedModel.add(item)

		item2 = {id: 3, faviconLink: null, urlHash: 'hi', test: 'hoho'}
		@FeedModel.add(item2)

		expect(@FeedModel.getByUrlHash('hi').id).toBe(3)
		expect(@FeedModel.getByUrlHash('hi').test).toBe('hoho')
		expect(@FeedModel.getById(3).id).toBe(3)
		expect(@FeedModel.getById(3).test).toBe('hoho')
		expect(@FeedModel.size()).toBe(1)


	it 'should update normally', =>
		item = {id: 3, faviconLink: null, urlHash: 'hi', test: 'heheh'}
		@FeedModel.add(item)

		item2 = {id: 3, faviconLink: null, urlHash: 'his', test: 'hoho'}
		@FeedModel.add(item2)

		expect(@FeedModel.getByUrlHash('hi')).toBe(undefined)
		expect(@FeedModel.getByUrlHash('his').id).toBe(3)
		expect(@FeedModel.getByUrlHash('his').test).toBe('hoho')
		expect(@FeedModel.getById(3).test).toBe('hoho')
		expect(@FeedModel.size()).toBe(1)


	it 'should clear invalidate the query cache on adding folder with name', =>
		item = {faviconLink: null, urlHash: 'hi', test: 'heheh', folderId: 0}
		
		expect(@FeedModel.getAllOfFolder(0).length).toBe(0)
		@FeedModel.add(item, false)

		expect(@FeedModel.getAllOfFolder(0).length).toBe(0)

		item2 = {faviconLink: null, urlHash: 'his', test: 'heheh', folderId: 0}
		@FeedModel.add(item2)

		expect(@FeedModel.getAllOfFolder(0).length).toBe(2)