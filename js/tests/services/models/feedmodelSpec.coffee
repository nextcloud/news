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


describe '_FeedModel', ->


	beforeEach module 'News'

	beforeEach inject (@_FeedModel, @_Model) =>


	it 'should extend model', =>
		expect(new @_FeedModel instanceof @_Model).toBeTruthy()


	it 'should bind an imagepath to the item if the url is empty', =>
		item =
			id: 3
			faviconLink: null
			urlHash: 'hi'
		utils =
			imagePath: jasmine.createSpy('utils')

		model = new @_FeedModel(utils)
		model.add(item)

		expect(utils.imagePath).toHaveBeenCalledWith('news', 'rss.svg')


	it 'should also update items when url is the same', =>
		utils =
			imagePath: jasmine.createSpy('utils')
		model = new @_FeedModel(utils)

		model.add({id: 2, faviconLink: null, urlHash: 'hi'})
		expect(model.size()).toBe(1)

		model.add({id: 2, faviconLink: null, urlHash: 'hi4'})
		expect(model.size()).toBe(1)
		expect(model.getById(2).urlHash).toBe('hi4')

		model.add({id: 3, faviconLink: 'hey', urlHash: 'hi4'})
		expect(model.size()).toBe(1)
		expect(model.getById(2)).toBe(undefined)
		expect(model.getById(3).faviconLink).toBe('hey')


	it 'should also remove the feed from the urlHash cache when its removed', =>
		utils =
			imagePath: jasmine.createSpy('utils')
		model = new @_FeedModel(utils)

		item = {id: 2, faviconLink: null, urlHash: 'hi'}
		model.add(item)

		expect(model.getByUrlHash('hi')).toBe(item)

		model.removeById(2)
		expect(model.getByUrlHash('hi')).toBe(undefined)